<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /**
     * Submit contact form
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $contact = Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'new',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tin nhắn đã được gửi thành công',
                'data' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'subject' => $contact->subject,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gửi tin nhắn'
            ], 500);
        }
    }

    /**
     * Get all contacts (for admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, email, or subject
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Get contact details
     */
    public function show(Contact $contact): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }

    /**
     * Update contact status
     */
    public function updateStatus(Request $request, Contact $contact): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,read,replied,resolved'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Trạng thái không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $contact->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công',
            'data' => $contact
        ]);
    }

    /**
     * Delete contact
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tin nhắn thành công'
        ]);
    }
}