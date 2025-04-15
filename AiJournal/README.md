# AI-Powered Personal Journal

A web-based personal journal platform that analyzes your entries for emotional sentiment, helping you understand your emotional trends over time.

## Features

- **User Authentication**: Secure registration and login system
- **Journal Entries**: Create, edit, and delete your personal journal entries
- **Sentiment Analysis**: AI-powered analysis of your journal entries' emotional content
- **Insights Dashboard**: Visual representation of your mood trends over time
- **Calendar View**: Track your journaling activity with a calendar interface
- **Privacy Controls**: Option to mark entries as private
- **Dark Mode**: Toggle between light and dark themes
- **Export Functionality**: Export your journal entries as text files

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Sentiment Analysis**: Python with TextBlob

## Setup Instructions

### Prerequisites

- PHP 7.0+
- MySQL 5.6+
- Python 3.6+ with TextBlob library

### Installation

1. **Clone or download the repository to your web server**

2. **Set up the database**
   - Create a MySQL database named `ai_journal`
   - Update database configuration in `includes/config.php` if needed
   - Run the database setup script by visiting `includes/setup.php` in your browser

3. **Install Python dependencies**
   ```
   pip install textblob
   # Download necessary NLTK data
   python -c "import nltk; nltk.download('punkt')"
   ```

4. **Set proper permissions**
   - Ensure the web server has write access to the necessary directories

5. **Access the application**
   - Navigate to the project URL in your browser
   - Register a new account to get started

## Usage

1. **Registration/Login**
   - Create a new account or login to your existing account

2. **Writing Journal Entries**
   - Click "New Entry" to create a new journal entry
   - Write your thoughts in the text area
   - Save your entry to analyze its sentiment

3. **Viewing Insights**
   - Navigate to the Insights page to see mood trends
   - Explore weekly summaries and mood distribution

4. **Using the Calendar**
   - Visit the Calendar view to see entries by date
   - Click on dates with entries to read or edit them

5. **Managing Settings**
   - Update privacy preferences
   - Export journal entries
   - Toggle dark mode

## Security Considerations

- All passwords are securely hashed using PHP's `password_hash` function
- SQL prepared statements are used to prevent SQL injection attacks
- Input sanitization is implemented throughout the application

## License

This project is developed for academic purposes.

## Credits

- Sentiment analysis powered by [TextBlob](https://textblob.readthedocs.io/)
- Charts created using [Chart.js](https://www.chartjs.org/) 