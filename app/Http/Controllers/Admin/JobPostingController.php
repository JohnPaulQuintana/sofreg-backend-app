<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    public function career()
    {
        $jobs = JobPosting::with(['responsibilities','requirements'])->orderBy('created_at', 'desc')->get();
        return response()->json(['jobs' => $jobs]);
    }
    public function index()
    {
        $jobs = JobPosting::with(['responsibilities','requirements'])->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['jobs' => $jobs]);
    }
    //create job
    public function job(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:open,close',
            'description' => 'nullable|string',
            'date_posted' => 'required|date',
            'address' => 'nullable|string|max:255',
            'responsibilities' => 'nullable|array',
            'responsibilities.*' => 'nullable|string',
            'requirements' => 'nullable|array',
            'requirements.*' => 'nullable|string',
        ]);

        $job = new JobPosting();
        $job->code = strtolower(strtok($validated['title'], ' ')); // gets first word and lowers it
        $job->title = $validated['title'];
        $job->status = $validated['status'];
        $job->description = $validated['description'] ?? null;
        $job->date_posted = $validated['date_posted'];
        $job->address = $validated['address'] ?? null;
        $job->save();

        // Save responsibilities
        if (!empty($validated['responsibilities'])) {
            foreach ($validated['responsibilities'] as $item) {
                if (!empty($item)) {
                    $job->responsibilities()->create(['responsibility' => $item]);
                }
            }
        }

        // Save requirements
        if (!empty($validated['requirements'])) {
            foreach ($validated['requirements'] as $item) {
                if (!empty($item)) {
                    $job->requirements()->create(['requirement' => $item]);
                }
            }
        }

        return response()->json(['message' => 'Job created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:open,close',
            'description' => 'nullable|string',
            'date_posted' => 'required|date',
            'address' => 'nullable|string',
            'responsibilities' => 'array',
            'requirements' => 'array',
            'responsibilities.*' => 'string',
            'requirements.*' => 'string',
        ]);

        $job = JobPosting::findOrFail($id);

        $job->title = $validated['title'];
        $job->code = strtolower(strtok($validated['title'], ' ')); // auto-generate code from title
        $job->status = $validated['status'];
        $job->description = $validated['description'] ?? null;
        $job->date_posted = $validated['date_posted'];
        $job->address = $validated['address'] ?? null;
        $job->save();

        // Delete old and recreate related responsibilities and requirements
        $job->responsibilities()->delete();
        $job->requirements()->delete();

        foreach ($validated['responsibilities'] ?? [] as $item) {
            $job->responsibilities()->create(['responsibility' => $item]);
        }

        foreach ($validated['requirements'] ?? [] as $item) {
            $job->requirements()->create(['requirement' => $item]);
        }

        return response()->json(['message' => 'Job updated successfully.']);
    }

    public function delete($id)
    {

        $job = JobPosting::where('id', $id)->first();

        if (!$job) {
            return response()->json(['message' => 'jobs not found'], 404);
        }

        $job->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
