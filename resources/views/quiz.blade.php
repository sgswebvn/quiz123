<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-5xl mx-auto px-4">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold">Quiz Tin Học</h1>
                <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg font-medium">
                    Thời gian: <span id="timer">45:00</span>
                </div>
            </div>
            
            <!-- Progress bar -->
            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-8">
                <div id="progress" class="bg-indigo-600 h-2.5 rounded-full transition-all" style="width: 2%"></div>
            </div>

            <form id="quizForm" action="{{ route('quiz.submit') }}" method="POST">
                @csrf
                @foreach ($questions as $index => $question)
                    @php
                        $multipleCorrect = $question->answers->where('is_correct', true)->count() > 1;
                        $inputType = $multipleCorrect ? 'checkbox' : 'radio';
                        $inputName = $multipleCorrect ? "question_{$question->id}[]" : "question_{$question->id}";
                    @endphp
                    <div class="question-card bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 {{ $index !== 0 ? 'hidden' : '' }}" data-index="{{ $index }}">
                        <p class="text-xl font-medium mb-2 leading-relaxed">
                            Câu {{ $index + 1 }}: {{ $question->question_text }}
                        </p>
                        @if($multipleCorrect)
                            <p class="text-sm font-semibold text-emerald-600 mb-6 flex items-center"><i class="fas fa-check-double mr-2"></i> Câu hỏi có nhiều đáp án, vui lòng đánh dấu TẤT CẢ đáp án đúng.</p>
                        @else
                            <p class="text-sm font-semibold text-gray-400 mb-6">Chọn 1 đáp án chính xác nhất.</p>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($question->answers as $answer)
                                <label class="flex items-center p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-indigo-50 transition answer-label">
                                    <input type="{{ $inputType }}" name="{{ $inputName }}" value="{{ $answer->id }}" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500 rounded{{ $multipleCorrect ? '' : '-full' }}">
                                    <span class="ml-4 text-lg">{{ $answer->answer_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-between mt-8">
                    <button type="button" id="prevBtn" class="px-8 py-4 bg-gray-300 text-gray-800 rounded-lg font-semibold hover:bg-gray-400 transition hidden">Câu trước</button>
                    <button type="button" id="nextBtn" class="px-10 py-4 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition">Câu tiếp theo</button>
                    <button type="submit" id="submitBtn" class="px-10 py-4 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition hidden shadow-lg">Nộp bài & Xem kết quả</button>
                </div>
            </form>
            <br>
            <br>
            <br>
            <br>
             <div class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-2 justify-center">
                @foreach (range(1, $questions->count()) as $num)
                    <button type="button" onclick="goToQuestion({{ $num - 1 }})"
                            class="w-10 h-10 rounded-full bg-gray-200 hover:bg-indigo-600 hover:text-white transition text-sm font-medium question-btn {{ $num == 1 ? 'bg-indigo-600 text-white' : '' }}"
                            data-question="{{ $num - 1 }}">
                        {{ $num }}
                    </button>
                @endforeach
            </div>

          
        </div>
    </div>

    <script>
        let current = 0;
        const questions = document.querySelectorAll('.question-card');
        const total = questions.length;
        const progress = document.getElementById('progress');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const questionBtns = document.querySelectorAll('.question-btn');

        const answered = new Set();

        let timeLeft = 45 * 60;
        const timerInterval = setInterval(() => {
            timeLeft--;
            const min = Math.floor(timeLeft / 60);
            const sec = timeLeft % 60;
            document.getElementById('timer').textContent = `${min}:${sec < 10 ? '0' : ''}${sec}`;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.getElementById('quizForm').submit();
            }
        }, 1000);

        function updateUI() {
            questions.forEach((q, i) => q.classList.toggle('hidden', i !== current));
            if (total > 0) {
                progress.style.width = `${((current + 1) / total) * 100}%`;
            }
            prevBtn.classList.toggle('hidden', current === 0);
            nextBtn.classList.toggle('hidden', current === total - 1 || total === 0);
            submitBtn.classList.toggle('hidden', current !== total - 1);

            questionBtns.forEach((btn, i) => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'bg-emerald-500', 'text-white');
                if (i === current) {
                    btn.classList.add('bg-indigo-600', 'text-white');
                } else if (answered.has(i)) {
                    btn.classList.add('bg-emerald-500', 'text-white');
                } else {
                    btn.classList.add('bg-gray-200', 'text-gray-800');
                }
            });
        }

        function goToQuestion(index) {
            current = index;
            updateUI();
        }

        document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', () => {
                const name = input.name;
                const questionIndex = Array.from(questions).findIndex(q => {
                    return q.querySelector(`input[name="${name}"]`);
                });
                
                if (questionIndex !== -1) {
                    if (input.type === 'checkbox') {
                        // Escape [] in name for querySelector
                        const safeName = name.replace(/\[/g, '\\[').replace(/\]/g, '\\]');
                        const anyChecked = document.querySelector(`input[name="${safeName}"]:checked`);
                        if (anyChecked) {
                            answered.add(questionIndex);
                        } else {
                            answered.delete(questionIndex);
                        }
                    } else {
                        answered.add(questionIndex);
                    }
                    updateUI();
                }
            });
        });

        if (total > 0) {
            prevBtn.addEventListener('click', () => { if (current > 0) current--; updateUI(); });
            nextBtn.addEventListener('click', () => { if (current < total - 1) current++; updateUI(); });
            updateUI();
        }
    </script>
</x-app-layout>