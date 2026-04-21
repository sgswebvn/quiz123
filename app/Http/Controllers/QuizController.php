<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class QuizController extends Controller
{
    public function index()
    {
        if (!Session::has('quiz_start_time')) {
            Session::put('quiz_start_time', now());
        }

        $questions = Question::with('answers')->inRandomOrder()->take(50)->get();

        Session::put('quiz_question_ids', $questions->pluck('id')->toArray());

        return view('quiz', compact('questions'));
    }

    public function submit(Request $request)
    {
        try {
            $startTime = Session::get('quiz_start_time');
            $questionIdsToScore = Session::get('quiz_question_ids', []);
            
            // Đảm bảo là mảng
            if (!is_array($questionIdsToScore)) {
                $questionIdsToScore = (array) $questionIdsToScore;
            }

            $timeTakenSeconds = 0;
            if ($startTime) {
                try {
                    $parsedStart = \Carbon\Carbon::parse($startTime);
                    $timeTakenSeconds = (int) $parsedStart->diffInSeconds(now());
                } catch (\Exception $e) {
                    $timeTakenSeconds = 0; // fallback nếu parse lỗi
                }
            }
            
            if ($timeTakenSeconds < 0) {
                $timeTakenSeconds = 0;
            }

            // Truy vấn database xem có lấy được câu hỏi và đáp án không
            $questions = Question::with('answers')->whereIn('id', $questionIdsToScore)->get();
            
            $score = 0;
            $total = count($questionIdsToScore) > 0 ? count($questionIdsToScore) : 50;

            foreach ($questions as $question) {
                // Lấy ID đáp án đúng
                $correctAnswerIds = $question->answers
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->map(fn($id) => (string)$id)
                    ->sort()
                    ->values()
                    ->toArray();
                
                // Lấy ID đáp án người dùng chọn
                $userAnswers = $request->input('question_' . $question->id);
                if (!is_array($userAnswers)) {
                    $userAnswers = $userAnswers ? [(string)$userAnswers] : [];
                } else {
                    $userAnswers = collect($userAnswers)
                        ->map(fn($id) => (string)$id)
                        ->sort()
                        ->values()
                        ->toArray();
                }

                // Chấm điểm
                if (!empty($correctAnswerIds) && $correctAnswerIds === $userAnswers) {
                    $score++;
                }
            }

            $result = Result::create([
                'user_id'    => Auth::id(),
                'score'      => $score,
                'total'      => $total,
                'time_taken' => $timeTakenSeconds,
            ]);

            Session::forget('quiz_start_time');
            Session::forget('quiz_question_ids');

            return redirect()->route('quiz.result', $result->id);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Quiz Submit Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return back()->with('error', 'Có lỗi hệ thống trong quá trình nộp bài: ' . $e->getMessage());
        }
    }

    public function showResult($id)
    {
        $result = Result::findOrFail($id);
        return view('result', compact('result'));
    }
}