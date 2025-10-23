<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts
     */
    public function index(Request $request): View
    {
        $query = Contact::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')
                         ->paginate(15);

        // Get stats
        $stats = [
            'total' => Contact::count(),
            'new' => Contact::where('status', 'new')->count(),
            'read' => Contact::where('status', 'read')->count(),
            'replied' => Contact::where('status', 'replied')->count(),
            'resolved' => Contact::where('status', 'resolved')->count(),
        ];

        return view('admin.contacts.index', compact('contacts', 'stats'));
    }

    /**
     * Display the specified contact
     */
    public function show(Contact $contact): View
    {
        // Mark as read if it's new
        if ($contact->status === 'new') {
            $contact->markAsRead();
        }

        return view('admin.contacts.show', compact('contact'));
    }

    /**
     * Update contact status
     */
    public function updateStatus(Request $request, Contact $contact): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:new,read,replied,resolved'
        ]);

        $contact->update(['status' => $request->status]);

        if ($request->status === 'replied') {
            $contact->update([
                'replied_at' => now(),
                'replied_by' => auth()->id()
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công');
    }

    /**
     * Remove the specified contact
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')
                        ->with('success', 'Xóa tin nhắn liên hệ thành công');
    }
}