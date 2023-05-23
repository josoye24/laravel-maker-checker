<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\RequestNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\UserProfile;
use App\Models\AdminMapping;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Log;



class UserRequestController extends Controller
{

    public function index()
    {
        // Retrieve all requests
        $requests = UserRequest::all();


        $result = [];

        foreach ($requests as $requestObject) {
            $initiatedByName = User::find($requestObject->initiated_by)->name;
            $approvedByName = $requestObject->approved_by ? User::find($requestObject->approved_by)->name : null;

            $result[] = [
                'id' => $requestObject->id,
                'user_id' => $requestObject->user_id,
                'initiated_by' => $requestObject->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'initiator_comment' => $requestObject->initiator_comment,
                'request_type' => $requestObject->request_type,
                'request_data' => $requestObject->request_data,
                'status' => $requestObject->status,
                'approved_by' => $requestObject->approved_by,
                'approved_by_name' => $approvedByName,
                'approval_comment' => $requestObject->approval_comment,
                'created_at' => $requestObject->created_at,
                'updated_at' => $requestObject->updated_at,
            ];
        }

        return response()->api($result, true, 'Requests retrieved successfully', 200);
    }

    public function getRequestbyID($id)
    {
        // Find the request
        $requestObject = UserRequest::find($id);

        // check that the request exists
        if (!$requestObject) {
            return response()->api(null, false, 'Invalid request id', 422);
        }

        $initiatedByName = User::find($requestObject->initiated_by)->name;
        $approvedByName = $requestObject->approved_by ? User::find($requestObject->approved_by)->name : null;

        $result = [
            'id' => $requestObject->id,
            'user_id' => $requestObject->user_id,
            'initiated_by' => $requestObject->initiated_by,
            'initiated_by_name' => $initiatedByName,
            'initiator_comment' => $requestObject->initiator_comment,
            'request_type' => $requestObject->request_type,
            'request_data' => $requestObject->request_data,
            'status' => $requestObject->status,
            'approved_by' => $requestObject->approved_by,
            'approved_by_name' => $approvedByName,
            'approval_comment' => $requestObject->approval_comment,
            'created_at' => $requestObject->created_at,
            'updated_at' => $requestObject->updated_at,
        ];

        return response()->api($result, true, 'Request retrieved successfully', 200);
    }


    public function getRequestbyStatus(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        // Find the request
        $requestObject = UserRequest::where('status', $validatedData['status'])->get();

        $result = [];

        foreach ($requestObject as $reqObj) {
            $initiatedByName = User::find($reqObj->initiated_by)->name;
            $approvedByName = $reqObj->approved_by ? User::find($reqObj->approved_by)->name : null;

            $result[] = [
                'id' => $reqObj->id,
                'user_id' => $reqObj->user_id,
                'initiated_by' => $reqObj->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'initiator_comment' => $reqObj->initiator_comment,
                'request_type' => $reqObj->request_type,
                'request_data' => $reqObj->request_data,
                'status' => $reqObj->status,
                'approved_by' => $reqObj->approved_by,
                'approved_by_name' => $approvedByName,
                'approval_comment' => $reqObj->approval_comment,
                'created_at' => $reqObj->created_at,
                'updated_at' => $reqObj->updated_at,
            ];
        }

        return response()->api($result, true, 'Request retrieved successfully', 200);
    }

    public function getRequestbyInitiatorId(Request $request)
    {
        $validatedData = $request->validate([
            'admin_id' => 'required'
        ]);

        //check that the admin exist
        $admin = User::find($validatedData['admin_id']);

        if (!$admin) {
            return response()->api(null, false, 'Invalid admin id', 422);
        }

        // Find the request
        $requestObject = UserRequest::where('initiated_by', $validatedData['admin_id'])->get();

        $result = [];

        foreach ($requestObject as $reqObj) {
            $initiatedByName = User::find($reqObj->initiated_by)->name;
            $approvedByName = $reqObj->approved_by ? User::find($reqObj->approved_by)->name : null;

            $result[] = [
                'id' => $reqObj->id,
                'user_id' => $reqObj->user_id,
                'initiated_by' => $reqObj->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'initiator_comment' => $reqObj->initiator_comment,
                'request_type' => $reqObj->request_type,
                'request_data' => $reqObj->request_data,
                'status' => $reqObj->status,
                'approved_by' => $reqObj->approved_by,
                'approved_by_name' => $approvedByName,
                'approval_comment' => $reqObj->approval_comment,
                'created_at' => $reqObj->created_at,
                'updated_at' => $reqObj->updated_at,
            ];
        }

        return response()->api($result, true, 'Request retrieved successfully', 200);
    }

    public function getRequestbyInitiatorIdStatus(Request $request)
    {
        $validatedData = $request->validate([
            'admin_id' => 'required',
            'status' => 'required|in:pending,approved,rejected'
        ]);


        //check that the admin exist
        $admin = User::find($validatedData['admin_id']);

        if (!$admin) {
            return response()->api(null, false, 'Invalid admin id', 422);
        }

        // Find the request
        $requestObject = UserRequest::where('initiated_by', $validatedData['admin_id'])->where('status', $validatedData['status'])->get();


        $result = [];

        foreach ($requestObject as $reqObj) {
            $initiatedByName = User::find($reqObj->initiated_by)->name;
            $approvedByName = $reqObj->approved_by ? User::find($reqObj->approved_by)->name : null;

            $result[] = [
                'id' => $reqObj->id,
                'user_id' => $reqObj->user_id,
                'initiated_by' => $reqObj->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'initiator_comment' => $reqObj->initiator_comment,
                'request_type' => $reqObj->request_type,
                'request_data' => $reqObj->request_data,
                'status' => $reqObj->status,
                'approved_by' => $reqObj->approved_by,
                'approved_by_name' => $approvedByName,
                'approval_comment' => $reqObj->approval_comment,
                'created_at' => $reqObj->created_at,
                'updated_at' => $reqObj->updated_at,
            ];
        }

        return response()->api($result, true, 'Request retrieved successfully', 200);
    }

    public function getRequestbyAuthorizerId(Request $request)
    {
        $validatedData = $request->validate([
            'admin_id' => 'required'
        ]);

        //check that the admin exist
        $admin = User::find($validatedData['admin_id']);

        if (!$admin) {
            return response()->api(null, false, 'Invalid admin id', 422);
        }

        // Find all request the admin is mapped to the iniziator
        $mappingIds  = AdminMapping::where('mapped_to', $validatedData['admin_id'])->where('status', 'approved')->pluck('admin_id');

        $requestObject = UserRequest::whereIn('initiated_by', $mappingIds)->get();


        $result = [];

        foreach ($requestObject as $reqObj) {
            $initiatedByName = User::find($reqObj->initiated_by)->name;
            $approvedByName = $reqObj->approved_by ? User::find($reqObj->approved_by)->name : null;

            $result[] = [
                'id' => $reqObj->id,
                'user_id' => $reqObj->user_id,
                'initiated_by' => $reqObj->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'initiator_comment' => $reqObj->initiator_comment,
                'request_type' => $reqObj->request_type,
                'request_data' => $reqObj->request_data,
                'status' => $reqObj->status,
                'approved_by' => $reqObj->approved_by,
                'approved_by_name' => $approvedByName,
                'approval_comment' => $reqObj->approval_comment,
                'created_at' => $reqObj->created_at,
                'updated_at' => $reqObj->updated_at,
            ];
        }

        return response()->api($result, true, 'Request retrieved successfully', 200);
    }

    public function getRequestbyAuthorizerIdStatus(Request $request)
    {
        $validatedData = $request->validate([
            'admin_id' => 'required',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        //check that the admin exist
        $admin = User::find($validatedData['admin_id']);

        if (!$admin) {
            return response()->api(null, false, 'Invalid admin id', 422);
        }

        // Find all request by status the admin is mapped to the iniziator
        $mappingIds  = AdminMapping::where('mapped_to', $validatedData['admin_id'])->where('status', 'approved')->pluck('admin_id');

        $requestObject = UserRequest::whereIn('initiated_by', $mappingIds)->where('status', $validatedData['status'])->get();


        $result = [];

        foreach ($requestObject as $reqObj) {
            $initiatedByName = User::find($reqObj->initiated_by)->name;
            $approvedByName = $reqObj->approved_by ? User::find($reqObj->approved_by)->name : null;

            $result[] = [
                'id' => $reqObj->id,
                'user_id' => $reqObj->user_id,
                'initiated_by' => $reqObj->initiated_by,
                'initiated_by_name' => $initiatedByName,
                'initiator_comment' => $reqObj->initiator_comment,
                'request_type' => $reqObj->request_type,
                'request_data' => $reqObj->request_data,
                'status' => $reqObj->status,
                'approved_by' => $reqObj->approved_by,
                'approved_by_name' => $approvedByName,
                'approval_comment' => $reqObj->approval_comment,
                'created_at' => $reqObj->created_at,
                'updated_at' => $reqObj->updated_at,
            ];
        }

        return response()->api($result, true, 'Request retrieved successfully', 200);
    }


    public function create(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'user_id' => 'required_if:request_type,update,delete',
            'request_type' => 'required|in:create,update,delete',
            'request_data' => 'required_if:request_type,create,update',
            'initiator_comment' => 'required_if:request_type,delete',
        ]);


        // Convert the request data to a string representation
        $requestData = json_encode($validatedData['request_data'] ?? []);

        $authenticatedUser = Auth::guard('api')->user();

        // Extract the request type and data from the request
        $userId = $validatedData['user_id'] ?? null;
        $initiatedBy = $authenticatedUser->id;
        $comment = $request->initiator_comment ?? null;

        // check that the user exists
        if ($userId && !UserProfile::find($userId)) {
            return response()->api(null, false, 'Invalid user id', 422);
        }

        //check that request initiator is mapped to an admin
        $adminMapping = AdminMapping::where('admin_id', $initiatedBy)->where('status', 'approved')->first();
        if (!$adminMapping) {
            return response()->api(null, false, 'You must be mapped to an admin before you can initiate a request', 422);
        }

        // Create a new request
        $request = UserRequest::create([
            'user_id' => $userId,
            'initiated_by' => $initiatedBy,
            'initiator_comment' => $comment,
            'request_type' => $validatedData['request_type'],
            'request_data' => $requestData
        ]);

        // get email address of all admins the request initiator is mapped to
        $mappingIds = AdminMapping::where('admin_id', $initiatedBy)->where('status', 'approved')->pluck('mapped_to');
        $mappingEmails = User::whereIn('id', $mappingIds)->pluck('email');

        // email subject
        $subject = "Request Notification";

        $emailData = [
            'email_header' => 'New Request',
            'email_body' => 'A new request has been initiated, waiting for your approval.',
            'initiator_name' => $authenticatedUser->name,
            'initiator_comment' => $request->initiator_comment ?? ' ',
            'request_type' => $validatedData['request_type'],
            'id' => $request->id,
            'date' => $request->created_at,
            'email_template' => 'new_request_template'
        ];

        // Send email notification to all admins the request initiator is mapped to
        foreach ($mappingEmails as $admin) {
            SendEmailJob::dispatch($admin, $subject, $emailData)->onQueue('emails');
        }

        return response()->api($request, true, 'Request submitted successfully', 200);
    }

    public function approve(Request $request)
    {
        $validatedData = $request->validate([
            'request_id' => 'required'
        ]);

        // Find the request
        $requestObject = UserRequest::find($validatedData['request_id']);

        // check that the request exists
        if (!$requestObject) {
            return response()->api(null, false, 'Invalid request id', 422);
        }

        // check request is not already approve or rejected
        if ($requestObject->status != 'pending') {
            return response()->api(null, false, 'Request already processed', 422);
        }

        // check that only approved admin mapped to the request initiator can approve the request
        // Super admin does not need to be mapped to approve a request
        $authenticatedUser = Auth::guard('api')->user();

        if ($authenticatedUser->admin_role != 'superadmin') {
            $adminMapping = AdminMapping::where('admin_id', $requestObject->initiated_by)
                ->where('mapped_to', $authenticatedUser->id)
                ->where('status', 'approved')
                ->first();
            if (!$adminMapping) {
                return response()->api(null, false, 'You are not authorized to approve this request', 422);
            }
        }

        // Decode the JSON-encoded request_data to an array
        $requestData = json_decode($requestObject->request_data, true);

        // Update user information based on the request type
        switch ($requestObject->request_type) {
            case 'create':
                // check that the user does not already exist
                if (UserProfile::where('email', $requestData['email'])->first()) {
                    return response()->api(null, false, "User with {$requestData['email']} already exists", 422);
                }

                // Create a new user based on the request data
                UserProfile::create([
                    'first_name' => $requestData['first_name'],
                    'last_name' => $requestData['last_name'],
                    'email' => $requestData['email'],
                ]);
                break;

            case 'update':
                // Find the user
                $user = UserProfile::find($requestObject->user_id);

                //check user exist
                if (!$user) {
                    return response()->api(null, false, "The user ID on the request is invalid", 422);
                }

                // Update the user information based on the request data
                $user->update([
                    'email' => $requestData['email'] ?? $user->email,
                    'first_name' => $requestData['first_name'] ?? $user->first_name,
                    'last_name' => $requestData['last_name'] ?? $user->last_name,
                ]);

                break;
            case 'delete':
                // Find the user and delete it
                $user = UserProfile::find($requestObject->user_id);

                //check user exist
                if (!$user) {
                    return response()->api(null, false, "The user ID on the request is invalid", 422);
                }

                $user->delete();
                break;
        }

        // Mark the request as approved
        $requestObject->status = 'approved';
        $requestObject->approved_by = $authenticatedUser->id;
        $requestObject->approval_comment = $request->approval_comment ?? null;
        $requestObject->save();


        // email subject
        $subject = "Request Notification - Approved";

        $emailData = [
            'email_header' => 'Request Approved',
            'email_body' => 'Your request has been approved and processed successfully.',
            'approval_name' => $authenticatedUser->name,
            'approval_comment' => $request->approval_comment ?? ' ',
            'id' => $requestObject->id,
            'date' => $requestObject->updated_at,
            'email_template' => 'approve_request_template'
        ];

        $initiatorEmail = User::find($requestObject->initiated_by)->email;

        // Send email notification to request initiator
        SendEmailJob::dispatch($initiatorEmail, $subject, $emailData)->onQueue('emails');

        return response()->api($requestObject, true, 'Request approved successfully', 200);
    }

    public function decline(Request $request)
    {
        $validatedData = $request->validate([
            'request_id' => 'required'
        ]);

        // Find the request
        $requestObject = UserRequest::find($validatedData['request_id']);

        // check that the request exists
        if (!$requestObject) {
            return response()->api(null, false, 'Invalid request id', 422);
        }

        // check that request is not already approve or rejected
        if ($requestObject->status != 'pending') {
            return response()->api(null, false, 'Request already processed', 422);
        }


        // check that only approved admin mapped to the request initiator can decline the request
        // Super admin does not need to be mapped to approve a request
        $authenticatedUser = Auth::guard('api')->user();

        if ($authenticatedUser->admin_role != 'superadmin') {
            $adminMapping = AdminMapping::where('admin_id', $requestObject->initiated_by)
                ->where('mapped_to', $authenticatedUser->id)
                ->where('status', 'approved')
                ->first();
            if (!$adminMapping) {
                return response()->api(null, false, 'You are not authorized to decline this request', 422);
            }
        }

        // Mark the request as rejected
        $requestObject->status = 'rejected';
        $requestObject->approved_by = $authenticatedUser->id;
        $requestObject->approval_comment = $request->approval_comment ?? null;
        $requestObject->save();


        // email subject
        $subject = "Request Notification - Declined";

        $emailData = [
            'email_header' => 'Request Declined',
            'email_body' => 'Your request has been declined and not processed.',
            'decline_name' => $authenticatedUser->name,
            'decline_comment' => $request->approval_comment ?? ' ',
            'id' => $requestObject->id,
            'date' => $requestObject->updated_at,
            'email_template' => 'decline_request_template'
        ];

        $initiatorEmail = User::find($requestObject->initiated_by)->email;
        
        // Send email notification to request initiator
        SendEmailJob::dispatch($initiatorEmail, $subject, $emailData)->onQueue('emails');

        return response()->api($requestObject, true, 'Request declined successfully', 200);
    }
}
