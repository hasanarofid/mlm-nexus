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
                   $currentUser->email === 'admin@talenta52.com' || 
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
                    'username' => $u->username ? '@' . $u->username : '@' . strtolower(explode(' ', $u->name)[0]),
                    'email' => $u->email,
                    'package_name' => $u->package_name ?? 'Basic',
                    'direct_count' => $g2Count,
                    'joined_at' => $u->created_at->format('d M Y, H:i'),
                ];
            });

        // Calculate team breakdown by generation depth (Generasi 1 s/d Generasi 15)
        $generations = $this->calculateGenerations($focusedUser->id);

        // Search options for quick focus selector (Admin: all users, Member: self + downlines)
        $allUsers = $allowedUserQuery->select('id', 'name', 'username', 'email')->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username ? '@' . $u->username : ('@' . strtolower(explode(' ', $u->name)[0])),
                'label' => $u->name . ' (' . ($u->username ? '@' . $u->username : $u->email) . ')',
            ];
        });

        return Inertia::render('Admin/Genealogy/Index', [
            'focus_user' => [
                'id' => $focusedUser->id,
                'name' => $focusedUser->name,
                'username' => $focusedUser->username ? '@' . $focusedUser->username : '@' . strtolower(explode(' ', $focusedUser->name)[0]),
                'package_name' => $focusedUser->package_name ?? 'Partner',
                'active_tier' => $focusedUser->getActiveTier(),
                'total_direct' => count($directDownlines),
                'total_team' => array_sum(array_column($generations, 'count')),
            ],
            'is_admin' => $isAdmin,
            'direct_downlines' => $directDownlines,
            'generations' => $generations,
            'all_users' => $allUsers,
        ]);
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
     * Recursively calculate team members count up to 10 generations depth.
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
                ];
                continue;
            }

            $downlineIds = User::whereIn('parent_id', $currentIds)->pluck('id')->toArray();
            $result[] = [
                'generation' => $gen,
                'label' => 'Generasi ' . $gen,
                'count' => count($downlineIds),
            ];

            $currentIds = $downlineIds;
        }

        return $result;
    }
}
