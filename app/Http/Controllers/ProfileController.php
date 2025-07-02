<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        return response()->json(Auth::user(), 200);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!($user instanceof \App\Models\User)) {
            $user = \App\Models\User::find($user->id);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|string|min:3|max:100',
            'phone'      => 'nullable|string|max:15',
            'birth_date' => 'nullable|date',
            'image'      => 'nullable|image|max:2048', // validamos como imagen real
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->fill($request->only(['name', 'phone', 'birth_date']));

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->image = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user
        ], 200);
    }
}
