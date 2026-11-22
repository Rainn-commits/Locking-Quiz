<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'action'  => 'sometimes|string|max:50',
            'user_id' => 'sometimes|integer',
        ]);

        return AuditLog::with('user:id,name,email,role')
            ->when($request->action, fn ($q, $action) => $q->where('action', $action))
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->latest('created_at')
            ->paginate(20);
    }
}
