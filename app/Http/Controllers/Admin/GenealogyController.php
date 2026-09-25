<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GenealogyController extends Controller
{
    /**
     * Display the Matahari / Level genealogy network directory.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user() ?: User::first();
        
        $isAdmin = $currentUser->username === 'admin' || 
                   $currentUser->email === 'admin@nexuscommunity.id' || 
                   ($currentUser->roles && $currentUser->hasRole('admin'));

        // Fetch allowed user IDs for focus selector
        if ($isAdmin) {
            $allowedUserQuery = User::query();
            $allowedUserIds = null;
        } else {
            $downlineIds = $this->getAllDownlineIds($currentUser->id);
            $allowedUserIds = array_merge([$currentUser->id], $downlineIds);
            $allowedUserQuery = User::whereIn('id', $allowedUserIds);
        }

        $focusId = (int) $request->query('focus_id', $currentUser->id);

        // Security check: Non-admin can only focus on self or users in their downline tree
        if (!$isAdmin && $allowedUserIds !== null) {
            if (!in_array($focusId, $allowedUserIds)) {
                $focusId = $currentUser->id;
            }
        }

        $focusedUser = User::find($focusId) ?: $currentUser;

        // Fetch Direct Downlines (Generasi 1)
        $directDownlines = User::where('parent_id', $focusedUser->id)
            ->latest()
            ->get()
            ->map(function ($u) {
                // Count direct downlines of this child (Generasi 2)
                $g2Count = User::where('parent_id', $u->id)->count();
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username ?: strtolower(explode(' ', $u->name)[0]),
                    'email' => $u->email,
                    'package_name' => $u->package_name ?: 'Standard',
                    'direct_count' => $g2Count,
                    'joined_at' => $u->created_at->format('d M Y, H:i'),
                ];
            });

        // Calculate team breakdown by generation depth (Generasi 1 s/d Generasi 10)
        $generations = $this->calculateGenerations($focusedUser->id);

        // Build nested hierarchical sponsor tree
        $treeData = $this->buildHierarchicalTree($focusedUser, 10);

        // Search options for quick focus selector (Admin: all users, Member: self + downlines)
        $allUsers = $allowedUserQuery->select('id', 'name', 'username', 'email')->get()->map(function ($u) {
            $un = $u->username ?: strtolower(explode(' ', $u->name)[0]);
            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $un,
                'label' => $u->name . ' (@' . $un . ')',
            ];
        });

        return Inertia::render('Admin/Genealogy/Index', [
            'focus_user' => [
                'id' => $focusedUser->id,
                'name' => $focusedUser->name,
                'username' => $focusedUser->username ?: strtolower(explode(' ', $focusedUser->name)[0]),
                'package_name' => $focusedUser->package_name ?: 'Standard',
                'active_tier' => $focusedUser->getActiveTier(),
                'total_direct' => count($directDownlines),
                'total_team' => $treeData['total_downlines'] ?? array_sum(array_column($generations, 'count')),
            ],
            'is_admin' => $isAdmin,
            'tree_data' => $treeData,
            'direct_downlines' => $directDownlines,
            'generations' => $generations,
            'all_users' => $allUsers,
        ]);
    }

    /**
     * Recursively build hierarchical sponsor tree up to max generations.
     */
    private function buildHierarchicalTree(User $rootUser, int $maxGen = 10): array
    {
        $allUsersByParent = [];
        $currentIds = [$rootUser->id];
        $depth = 0;

        while (!empty($currentIds) && $depth < $maxGen) {
            $depth++;
            $users = User::whereIn('parent_id', $currentIds)
                ->orderBy('id', 'asc')
                ->get();

            if ($users->isEmpty()) {
                break;
            }

            foreach ($users as $u) {
                $allUsersByParent[$u->parent_id][] = $u;
            }

            $currentIds = $users->pluck('id')->toArray();
        }

        $buildNode = function ($user, $gen = 0) use (&$buildNode, &$allUsersByParent, $maxGen) {
            $childrenModels = ($gen < $maxGen) ? ($allUsersByParent[$user->id] ?? []) : [];
            $childrenNodes = [];
            $totalDownlines = 0;

            foreach ($childrenModels as $child) {
                $childNode = $buildNode($child, $gen + 1);
                $totalDownlines += 1 + ($childNode['total_downlines'] ?? 0);
                $childrenNodes[] = $childNode;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?: strtolower(explode(' ', $user->name)[0]),
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'package_name' => $user->package_name ?? 'Standard',
                'joined_at' => $user->created_at ? $user->created_at->format('d M Y') : '-',
                'generation' => $gen,
                'direct_count' => count($childrenNodes),
                'total_downlines' => $totalDownlines,
                'children' => $childrenNodes,
            ];
        };

        return $buildNode($rootUser, 0);
    }

    /**
     * Recursively fetch all downline IDs under a root user (under referral).
     */
    private function getAllDownlineIds($rootUserId): array
    {
        $allDownlines = [];
        $currentIds = [$rootUserId];

        while (!empty($currentIds)) {
            $downlineIds = User::whereIn('parent_id', $currentIds)->pluck('id')->toArray();
            if (empty($downlineIds)) {
                break;
            }
            $allDownlines = array_merge($allDownlines, $downlineIds);
            $currentIds = $downlineIds;
        }

        return $allDownlines;
    }

    /**
     * Recursively calculate team members and fetch tree objects up to 10 generations depth.
     */
    private function calculateGenerations($rootUserId): array
    {
        $result = [];
        $currentIds = [$rootUserId];

        for ($gen = 1; $gen <= 10; $gen++) {
            if (empty($currentIds)) {
                $result[] = [
                    'generation' => $gen,
                    'label' => 'Generasi ' . $gen,
                    'count' => 0,
                    'members' => [],
                ];
                continue;
            }

            $users = User::whereIn('parent_id', $currentIds)
                ->with('parent')
                ->latest()
                ->get();

            $downlineIds = $users->pluck('id')->toArray();

            $membersList = $users->map(function ($u) {
                // Get direct children of this user (G2 for this user)
                $children = User::where('parent_id', $u->id)->get()->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'username' => $child->username ? '@' . $child->username : '@' . strtolower(explode(' ', $child->name)[0]),
                        'package_name' => $child->package_name ?? 'Standard',
                        'joined_at' => $child->created_at->format('d M Y, H:i'),
                        'direct_count' => User::where('parent_id', $child->id)->count(),
                    ];
                });

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username ? '@' . $u->username : '@' . strtolower(explode(' ', $u->name)[0]),
                    'email' => $u->email,
                    'package_name' => $u->package_name ?? 'Standard',
                    'joined_at' => $u->created_at->format('d M Y, H:i'),
                    'parent_id' => $u->parent_id,
                    'parent_name' => $u->parent ? $u->parent->name : 'Sponsor Utama',
                    'direct_count' => count($children),
                    'children' => $children,
                ];
            });

            $result[] = [
                'generation' => $gen,
                'label' => 'Generasi ' . $gen,
                'count' => count($downlineIds),
                'members' => $membersList,
            ];

            $currentIds = $downlineIds;
        }

        return $result;
    }
}
