<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with('answers')->paginate(10);
        return view('admin.questions.index', compact('questions'));
    }

    public function create()
    {
        return view('admin.questions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string',
            'correct_answers' => 'required|array|min:1',
        ], [
            'correct_answers.required' => 'Vui lòng chọn ít nhất một đáp án đúng.'
        ]);

        $question = Question::create(['question_text' => $request->question_text]);

        foreach ($request->answers as $key => $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['text'],
                'is_correct' => in_array($key, $request->correct_answers),
            ]);
        }

        return redirect()->route('admin.questions.index')->with('success', 'Câu hỏi đã được tạo!');
    }

    public function edit(Question $question)
    {
        $question->load('answers');
        return view('admin.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question_text' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string',
            'correct_answers' => 'required|array|min:1',
        ], [
            'correct_answers.required' => 'Vui lòng chọn ít nhất một đáp án đúng.'
        ]);

        $question->update(['question_text' => $request->question_text]);

        $question->answers()->delete();

        foreach ($request->answers as $key => $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['text'],
                'is_correct' => in_array($key, $request->correct_answers),
            ]);
        }

        return redirect()->route('admin.questions.index')->with('success', 'Câu hỏi đã được cập nhật!');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.questions.index')->with('success', 'Đã xoá câu hỏi!');
    }
}
