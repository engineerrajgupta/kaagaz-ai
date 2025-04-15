document.addEventListener('DOMContentLoaded', function() {
    // Character counter for journal textarea
    const journalTextarea = document.getElementById('journal-content');
    const charCounter = document.getElementById('char-counter');
    
    if (journalTextarea && charCounter) {
        journalTextarea.addEventListener('input', function() {
            const charCount = this.value.length;
            charCounter.textContent = charCount + ' characters';
        });
        
        // Trigger once to initialize counter
        journalTextarea.dispatchEvent(new Event('input'));
    }
    
    // Auto-fill today's date in journal entry form
    const dateInput = document.getElementById('entry-date');
    if (dateInput && !dateInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        dateInput.value = `${year}-${month}-${day}`;
    }
    
    // Delete confirmation for journal entries
    const deleteButtons = document.querySelectorAll('.delete-entry');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this entry? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Delete all entries confirmation
    const deleteAllBtn = document.getElementById('delete-all-entries');
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function(e) {
            if (!confirm('WARNING: Are you sure you want to delete ALL your journal entries? This action CANNOT be undone!')) {
                e.preventDefault();
            }
        });
    }
    
    // Autosave draft using localStorage
    if (journalTextarea) {
        // Load existing draft if it exists
        const savedDraft = localStorage.getItem('journal_draft');
        if (savedDraft && journalTextarea.value === '') {
            journalTextarea.value = savedDraft;
            journalTextarea.dispatchEvent(new Event('input'));
        }
        
        // Save draft every 5 seconds while typing
        let typingTimer;
        journalTextarea.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                localStorage.setItem('journal_draft', journalTextarea.value);
            }, 1000);
        });
        
        // Clear draft when form is submitted
        const journalForm = document.getElementById('journal-form');
        if (journalForm) {
            journalForm.addEventListener('submit', function() {
                localStorage.removeItem('journal_draft');
            });
        }
    }
    
    // Calendar functionality
    const calendarDays = document.querySelectorAll('.calendar-day');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            if (this.dataset.date) {
                window.location.href = 'pages/write.php?date=' + this.dataset.date;
            }
        });
    });
    
    // Random writing prompts
    const writingPrompts = [
        "What made you smile today?",
        "What's something challenging you're currently facing?",
        "What are you grateful for today?",
        "Describe a meaningful conversation you had recently.",
        "What's something new you learned today?",
        "How did you practice self-care today?",
        "What's something you're looking forward to?",
        "Describe your current mood and why you might feel this way.",
        "What was the highlight of your day?",
        "Is there something you wish you handled differently today?",
        "What boundaries did you set or maintain today?",
        "What made you laugh today?",
        "What's a goal you're working towards?",
        "Describe a moment of kindness you witnessed or participated in.",
        "How did you deal with stress today?"
    ];
    
    const promptContainer = document.getElementById('writing-prompt');
    if (promptContainer) {
        const randomIndex = Math.floor(Math.random() * writingPrompts.length);
        promptContainer.textContent = writingPrompts[randomIndex];
    }
    
    // Daily quote generator
    const quotes = [
        "The only way to do great work is to love what you do. - Steve Jobs",
        "Life is what happens when you're busy making other plans. - John Lennon",
        "The future belongs to those who believe in the beauty of their dreams. - Eleanor Roosevelt",
        "In the middle of difficulty lies opportunity. - Albert Einstein",
        "You must be the change you wish to see in the world. - Mahatma Gandhi",
        "The best way to predict the future is to create it. - Peter Drucker",
        "Happiness is not something ready-made. It comes from your own actions. - Dalai Lama",
        "The purpose of our lives is to be happy. - Dalai Lama",
        "The only impossible journey is the one you never begin. - Tony Robbins",
        "Believe you can and you're halfway there. - Theodore Roosevelt",
        "The way to get started is to quit talking and begin doing. - Walt Disney",
        "Life is really simple, but we insist on making it complicated. - Confucius",
        "You are never too old to set another goal or to dream a new dream. - C.S. Lewis",
        "The journey of a thousand miles begins with one step. - Lao Tzu",
        "The only person you are destined to become is the person you decide to be. - Ralph Waldo Emerson"
    ];
    
    const quoteContainer = document.getElementById('daily-quote');
    if (quoteContainer) {
        const randomIndex = Math.floor(Math.random() * quotes.length);
        quoteContainer.textContent = quotes[randomIndex];
    }
    
    // Private/Public toggle for journal entries
    const privacyToggles = document.querySelectorAll('.privacy-toggle');
    privacyToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const entryId = this.dataset.entryId;
            const isPrivate = this.checked ? 1 : 0;
            
            // Send AJAX request to update privacy setting
            fetch('includes/update_privacy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `entry_id=${entryId}&is_private=${isPrivate}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI as needed
                    this.parentElement.querySelector('.privacy-status').textContent = 
                        isPrivate ? 'Private' : 'Public';
                }
            })
            .catch(error => {
                console.error('Error updating privacy setting:', error);
            });
        });
    });
}); 