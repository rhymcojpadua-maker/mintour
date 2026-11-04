@extends('layouts.admin')
@section('title', $user->exists ? 'Edit user' : 'New user')
@section('heading', $user->exists ? 'Edit user' : 'New user')

@section('content')
    <div class="max-w-2xl">
        <form method="POST"
              action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
              class="space-y-6">
            @csrf
            @if($user->exists) @method('PUT') @endif

            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-5">
                <x-admin.form-field name="name" label="Full name" :value="$user->name" required />
                <x-admin.form-field name="email" label="Email" type="email" :value="$user->email" required />

                <x-admin.form-field name="role" label="Role" type="select" required :value="$user->role?->value ?? 'user'"
                    :options="['user' => 'Visitor', 'staff' => 'Staff', 'admin' => 'Administrator', 'super_admin' => 'Super Admin']" />

                <x-admin.form-field name="password" label="Password" type="password"
                                   :required="!$user->exists"
                                   hint="{{ $user->exists ? 'Leave blank to keep current password.' : 'Minimum 8 characters, letters and numbers.' }}" />
                <x-admin.form-field name="password_confirmation" label="Confirm password" type="password"
                                   :required="!$user->exists" />

                <x-admin.form-field name="is_active" label="Active" type="checkbox"
                                   :value="$user->is_active ?? true" hint="Disabled accounts cannot sign in." />
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-3 rounded-xl bg-slate-100 font-semibold text-sm">Cancel</a>
                <button class="px-6 py-3 rounded-xl bg-teal-700 text-white font-semibold text-sm">
                    {{ $user->exists ? 'Save changes' : 'Create user' }}
                </button>
            </div>
        </form>
    </div>
@endsection
5.8 Reviews (moderation)
