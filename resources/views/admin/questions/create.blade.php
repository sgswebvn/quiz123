@extends('layouts.admin')
@section('title', 'Tạo Câu Hỏi Mới')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 max-w-3xl">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800">Nội dung câu hỏi</h3>
        <p class="text-sm text-gray-500 mt-1">Câu hỏi có thể có nhiều đáp án (Hỗ trợ câu Đúng/Sai hoặc đa đáp án).</p>
    </div>
    <form action="{{ route('admin.questions.store') }}" method="POST" class="p-6" id="questionForm">
        @csrf
        
        <div class="mb-8">
            <textarea name="question_text" rows="3" placeholder="Nhập nội dung câu hỏi..." class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-4 text-gray-700 bg-gray-50 focus:bg-white" required>{{ old('question_text') }}</textarea>
            @error('question_text')<p class="text-sm text-red-600 mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
            @error('answers')<p class="text-sm text-red-600 mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
            @error('correct_answers')<p class="text-sm text-red-600 mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>@enderror
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Các đáp án lựa chọn</h4>
                <button type="button" id="addAnswerBtn" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-plus mr-1"></i> Thêm đáp án
                </button>
            </div>
            <div id="answersContainer" class="space-y-4">
                <!-- Javascript will render here -->
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.questions.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-100 transition-colors">Hủy Bỏ</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20 flex items-center">
                <i class="fas fa-save mr-2"></i> Lưu Câu Hỏi
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('answersContainer');
    const addBtn = document.getElementById('addAnswerBtn');
    let answerCount = 0;

    const oldAnswers = @json(old('answers', []));
    const oldCorrect = @json(old('correct_answers', []));

    function createAnswerRow(text = '', isCorrect = false) {
        const id = answerCount++;
        const row = document.createElement('div');
        row.className = 'answer-row flex items-center gap-4 bg-white p-4 rounded-xl border border-gray-200 hover:border-blue-300 transition-colors shadow-sm focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500';
        row.innerHTML = `
            <div class="flex items-center h-5 w-5 justify-center" title="Đánh dấu là đáp án đúng">
                <input type="checkbox" name="correct_answers[]" value="${id}" ${isCorrect ? 'checked' : ''} class="w-5 h-5 text-emerald-500 rounded focus:ring-emerald-500 border-gray-300 cursor-pointer">
            </div>
            <div class="flex-1 flex gap-3 items-center">
                <span class="index-label text-sm font-bold text-gray-400 bg-gray-100 w-8 h-8 flex items-center justify-center rounded-lg"></span>
                <input type="text" name="answers[${id}][text]" value="${text.replace(/"/g, '&quot;')}" placeholder="Nhập nội dung đáp án..." class="w-full border-0 focus:ring-0 p-0 text-gray-700" required>
                <button type="button" class="remove-btn text-red-400 hover:text-red-600 p-2" title="Xoá đáp án"><i class="fas fa-trash"></i></button>
            </div>
        `;

        row.querySelector('.remove-btn').addEventListener('click', function() {
            if (container.querySelectorAll('.answer-row').length > 2) {
                row.remove();
                updateLabels();
            } else {
                alert('Một câu hỏi phải có ít nhất 2 đáp án!');
            }
        });

        container.appendChild(row);
    }

    function updateLabels() {
        const rows = container.querySelectorAll('.answer-row');
        rows.forEach((row, index) => {
            row.querySelector('.index-label').textContent = String.fromCharCode(65 + index);
        });
    }

    if (Object.keys(oldAnswers).length > 0) {
        Object.keys(oldAnswers).forEach(key => {
            const isCorrect = oldCorrect.includes(key.toString());
            createAnswerRow(oldAnswers[key].text, isCorrect);
        });
    } else {
        // Default 4 answers on fresh load
        for(let i=0; i<4; i++) createAnswerRow();
    }
    
    updateLabels();

    addBtn.addEventListener('click', function() {
        createAnswerRow();
        updateLabels();
    });
});
</script>
@endsection
