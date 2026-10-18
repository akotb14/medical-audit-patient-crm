<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use Illuminate\Http\Request;

class FollowUpsController extends Controller
{
    //
    public function index()
    {
        $followUps = FollowUp::all();
        return view('followUps.index', compact('followUps'));
    }
    public function create(){
        return view('followUps.create');
    }
    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required',
        ]);
        $followUp = new FollowUp();
        $followUp->name = $request->name;
        $followUp->email = $request->email;
        $followUp->phone = $request->phone;
        $followUp->message = $request->message;
        $followUp->save();
        return redirect()->route('followUps.index')->with('success', 'تم اضافة المتابعة بنجاح');
    }
    public function show(FollowUp $followUp){
        return view('followUps.show', compact('followUp'));
    }
    public function edit(FollowUp $followUp){
        return view('followUps.edit', compact('followUp'));
    }
    public function update(Request $request, FollowUp $followUp){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required',
        ]);
        $followUp->update($request->all());
        return redirect()->route('followUps.index')->with('success', 'تم تحديث المتابعة بنجاح');
    }
    public function destroy(FollowUp $followUp){
        $followUp->delete();
        return redirect()->route('followUps.index')->with('success', 'تم حذف المتابعة بنجاح');
    }
}
