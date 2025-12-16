<x-app-layout>
    @section('header')
        Edit User
    @endsection

    <div class="max-w-xl mx-auto">
        <form method="POST" action="{{ route('users.update', $user) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <div class="col-span-full">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Full Name</label>
                        <div class="mt-2">
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email Address</label>
                        <div class="mt-2">
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">New Password (Optional)</label>
                        <div class="mt-2">
                            <input type="password" name="password" id="password" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirm New Password</label>
                        <div class="mt-2">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Role</label>
                        <div class="mt-2 space-y-2">
                            @foreach($roles as $role)
                                <div class="relative flex gap-x-3">
                                    <div class="flex h-6 items-center">
                                        <input id="role_{{ $role }}" name="roles[]" value="{{ $role }}" type="checkbox" {{ in_array($role, $userRole) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    </div>
                                    <div class="text-sm leading-6">
                                        <label for="role_{{ $role }}" class="font-medium text-gray-900">{{ $role }}</label>
                                    </div>
                                </div>
                            @endforeach
                            <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="flex items-center justify-between border-t border-gray-900/10 px-4 py-4 sm:px-8">
                @if($user->id !== auth()->id())
                    <button type="button" onclick="confirm('Are you sure you want to delete this user?') || event.preventDefault(); document.getElementById('delete-user-form').submit();" class="text-sm font-semibold leading-6 text-red-600 hover:text-red-500">Delete User</button>
                @else
                     <div></div> 
                @endif
                
                <div class="flex items-center gap-x-6">
                    <a href="{{ route('users.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save Changes</button>
                </div>
            </div>
        </form>

        <form id="delete-user-form" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-app-layout>
