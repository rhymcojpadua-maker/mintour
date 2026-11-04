@extends('layouts.admin')
@section('title', 'Users')
@section('heading', 'Users')

@section('content')
    <x-admin.page-header
        title="Users"
        subtitle="Manage accounts and roles. Only super admins can create users."
        :action-url="auth()->user()->role->value === 'super_admin' ? route('admin.users.create') : null"
        action-label="New user"
        :search-route="route('admin.users.index')"
        :search="$search ?? ''"
    />

    <x-admin.table :headers="['Name', 'Email', 'Role', 'Status', 'Joined', '']" :paginator="$users">
        @foreach($users as $u)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-linear-to-br from-teal-700 to-teal-500 grid place-items-center text-white text-xs font-bold">
                            {{ Str::of($u->name)->explode(' ')->map(fn($n) => $n[0])->take(2)->implode('') }}
                        </div>
                        <div class="font-semibold">{{ $u->name }}</div>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-500">{{ $u->email }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold
                        {{ $u->role->value === 'super_admin' ? 'bg-coral-300/60 text-coral-500' :
                           ($u->isAdmin() ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600') }}">
                        {{ $u->role->label() }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold
                        {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $u->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ $u->created_at->format('M j, Y') }}</td>
                <td class="px-4 py-3">
                    <x-admin.row-actions
                        :edit-route="route('admin.users.edit', $u)"
                        :delete-route="route('admin.users.destroy', $u)"
                    />
                </td>
            </tr>
        @endforeach
    </x-admin.table>
@endsection
