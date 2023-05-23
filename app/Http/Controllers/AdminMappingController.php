<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminMapping;



class AdminMappingController extends Controller
{

    public function index(Request $request)
    {
        // check that the mapping exists
        $mapping = AdminMapping::where('id', $request->id)->first();
        if (!$mapping) {
            return response()->api(null, false, 'Invalid mapping id', 422);
        }

        $adminName = User::find($mapping->admin_id)->name;
        $mappedToName = User::find($mapping->mapped_to)->name;
        $approvedByName = $mapping->approved_by ? User::find($mapping->approved_by)->name : null;
        $initiatedByName = User::find($mapping->initiated_by)->name;

        $result = [
            'id' => $mapping->id,
            'admin_id' => $mapping->admin_id,
            'admin_name' => $adminName,
            'mapped_to' => $mapping->mapped_to,
            'mapped_to_name' => $mappedToName,
            'approved_by' => $mapping->approved_by,
            'approved_by_name' => $approvedByName,
            'initiated_by' => $mapping->initiated_by,
            'initiated_by_name' => $initiatedByName,
            'status' => $mapping->status,
            'created_at' => $mapping->created_at,
            'updated_at' => $mapping->updated_at,
        ];

        return response()->api($result, true, 'Mapping gotten successfully', 200);
    }

    public function mapAdmin(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'admin_id' => 'required',
            'mapped_to' => 'required',
        ]);

        // Check that admin_id and mapped_to are not the same
        if ($validatedData['admin_id'] == $validatedData['mapped_to']) {
            return response()->api(null, false, 'Admin cannot be mapped to himself', 200);
        }

        // check that admin_id and mapped_to are valid admin users
        $admin = User::where('id', $validatedData['admin_id'])->first();
        $mappedTo = User::where('id', $validatedData['mapped_to'])->first();

        if (!$admin || !$mappedTo) {
            return response()->api(null, false, 'Invalid admin ids', 422);
        }


        // Check if the admin is already mapped to the provided mappedToId
        $existingMapping = AdminMapping::where('admin_id', $admin->id)
            ->where('mapped_to', $mappedTo->id)
            ->first();

        if ($existingMapping) {
            return response()->api(null, false, 'Admin is already mapped to the provided admin.', 422);
        }

        // check that supervisor admin cannot map to support admin and support admin cannot map to support admin
        if ($admin->admin_role == 'support' || $admin->admin_role == 'supervisor') {
            if ($mappedTo->admin_role == 'support') {
                return response()->api(null, false, 'Support or Supervisor admin cannot be mapped to support admin', 422);
            }
        }


        $authenticatedUser = Auth::guard('api')->user();

        // Map the admin to the admin
        $createMapping = $admin->mappedAdmin()->create([
            'admin_id' => $validatedData['admin_id'],
            'mapped_to' => $validatedData['mapped_to'],
            'initiated_by' => $authenticatedUser->id,
        ]);


        return response()->api($createMapping, true, 'Admin mapped successfully', 200);
    }


    public function approveAdminMapping(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'map_id' => 'required',
            'status' => 'required|in:pending,approved,rejected'
        ]);


        // check that the mapping exists
        $mapping = AdminMapping::where('id', $validatedData['map_id'])->first();
        if (!$mapping) {
            return response()->api(null, false, 'Invalid mapping id', 422);
        }

        //check that mapping is not already approved
        if ($mapping->status != 'pending') {
            return response()->api(null, false, 'Mapping already processed', 422);
        }

        $authenticatedUser = Auth::guard('api')->user();

        // check that only supervisor or superadmin can approve mapping
        if ($authenticatedUser->admin_role == 'support') {
            return response()->api(null, false, 'You are not authorized to approve this mapping', 422);
        }

        // update maping status
        $mapping->update([
            'status' => $validatedData['status'],
            'approved_by' => $authenticatedUser->id,
        ]);

        return response()->api($mapping, true, 'Mapping approved successfully', 200);
    }

    public function getAllMappings()
    {
        $mappings = AdminMapping::with('admin')->get();

        $groupedMappings = $mappings->groupBy('admin_id')->map(function ($items, $adminId) {
            $adminName = $items->first()->admin->name;
            $adminMappings = $items->map(function ($item) {
                $mappedToName = User::find($item->mapped_to)->name;
                $approvedByName = $item->approved_by ? User::find($item->approved_by)->name : null;
                $initiatedByName = User::find($item->initiated_by)->name;

                unset($item->admin);
                return [
                    'id' => $item->id,
                    'admin_id' => $item->admin_id,
                    'mapped_to' => $item->mapped_to,
                    'mapped_to_name' => $mappedToName,
                    'approved_by' => $item->approved_by,
                    'approved_by_name' => $approvedByName,
                    'initiated_by' => $item->initiated_by,
                    'initiated_by_name' => $initiatedByName,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return [
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'admin_mappings' => $adminMappings->toArray(),
            ];
        })->values();

        return response()->api($groupedMappings, true, 'All admin mappings gotten successfully', 200);
    }

    public function getAllMappingsByStatus(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $mappings = AdminMapping::where('status', $validatedData['status'])->with('admin')->get();

        $groupedMappings = $mappings->groupBy('admin_id')->map(function ($items, $adminId) {
            $adminName = $items->first()->admin->name;
            $adminMappings = $items->map(function ($item) {
                $mappedToName = User::find($item->mapped_to)->name;
                $approvedByName = $item->approved_by ? User::find($item->approved_by)->name : null;
                $initiatedByName = User::find($item->initiated_by)->name;

                unset($item->admin);
                return [
                    'id' => $item->id,
                    'admin_id' => $item->admin_id,
                    'mapped_to' => $item->mapped_to,
                    'mapped_to_name' => $mappedToName,
                    'approved_by' => $item->approved_by,
                    'approved_by_name' => $approvedByName,
                    'initiated_by' => $item->initiated_by,
                    'initiated_by_name' => $initiatedByName,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return [
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'admin_mappings' => $adminMappings->toArray(),
            ];
        })->values();

        return response()->api($groupedMappings, true, "All {$validatedData['status']} admin mappings gotten successfully", 200);
    }




    public function getMappingById(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'admin_id' => 'required',
        ]);

        // check that the mapping exists
        $mappings = AdminMapping::where('admin_id', $validatedData['admin_id'])->get();
        if (!$mappings) {
            return response()->api(null, false, 'Invalid admin id', 422);
        }

        $result = [];

        foreach ($mappings as $mapping) {
            $adminName = User::find($mapping->admin_id)->name;
            $mappedToName = User::find($mapping->mapped_to)->name;
            $approvedByName = $mapping->approved_by ? User::find($mapping->approved_by)->name : null;
            $initiatedByName = User::find($mapping->initiated_by)->name;

            $result[] = [
                'id' => $mapping->id,
                'admin_id' => $mapping->admin_id,
                'admin_name' => $adminName,
                'mapped_to' => $mapping->mapped_to,
                'mapped_to_name' => $mappedToName,
                'approved_by' => $mapping->approved_by,
                'approved_by_name' => $approvedByName,
                'initiated_by' => $mapping->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'status' => $mapping->status,
                'created_at' => $mapping->created_at,
                'updated_at' => $mapping->updated_at,
            ];
        }

        return response()->api($result, true, 'Admin user mappings gotten successfully', 200);
    }

    public function getMappingByIdStatus(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'admin_id' => 'required',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        // check that the mapping exists
        $checkAdminID = AdminMapping::where('admin_id', $validatedData['admin_id'])->get();
        if (!$checkAdminID) {
            return response()->api(null, false, 'Invalid admin id', 422);
        }


        $mappings = AdminMapping::where('status', $validatedData['status'])->with('admin')->get();

        $result = [];

        foreach ($mappings as $mapping) {
            $adminName = User::find($mapping->admin_id)->name;
            $mappedToName = User::find($mapping->mapped_to)->name;
            $approvedByName = $mapping->approved_by ? User::find($mapping->approved_by)->name : null;
            $initiatedByName = User::find($mapping->initiated_by)->name;

            $result[] = [
                'id' => $mapping->id,
                'admin_id' => $mapping->admin_id,
                'admin_name' => $adminName,
                'mapped_to' => $mapping->mapped_to,
                'mapped_to_name' => $mappedToName,
                'approved_by' => $mapping->approved_by,
                'approved_by_name' => $approvedByName,
                'initiated_by' => $mapping->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'status' => $mapping->status,
                'created_at' => $mapping->created_at,
                'updated_at' => $mapping->updated_at,
            ];
        }

        return response()->api($result, true, "{$validatedData['status']} Admin user mappings gotten successfully", 200);
    }
}
