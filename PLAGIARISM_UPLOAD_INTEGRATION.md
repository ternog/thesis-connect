# Plagiarism Checker Integration with Upload System

## Overview
The plagiarism checker is now fully integrated into the thesis upload process. Theses with high plagiarism scores (≥40% by default) will be automatically rejected during submission.

## Features Implemented

### 1. Automatic Plagiarism Check on Upload
- When a user submits a thesis, the system automatically checks the title and abstract for plagiarism
- Compares against all approved theses in the database
- Uses cosine similarity algorithm to calculate similarity percentage

### 2. Configurable Threshold
- Default threshold: 40% similarity
- Can be configured in `.env` file or `config/plagiarism.php`
- Theses with scores ≥ threshold are automatically rejected

### 3. Detailed Error Messages
- Users receive clear feedback when plagiarism is detected
- Shows the plagiarism score and threshold
- Lists up to 5 similar theses with their similarity scores
- Provides guidance to revise and resubmit

### 4. Preview Plagiarism Check
- Users can check plagiarism BEFORE submitting
- "Check Plagiarism Before Submitting" button in the form
- Shows real-time results without creating the thesis
- Helps users avoid rejection

### 5. Plagiarism Score Display
- Successful submissions show the plagiarism score
- Stored in the database for future reference
- Visible in activity logs

## Configuration

### Backend Configuration

**File:** `backend/config/plagiarism.php`

```php
return [
    // Maximum allowed similarity percentage (default: 40%)
    'threshold' => env('PLAGIARISM_THRESHOLD', 40),
    
    // Minimum similarity to report (default: 15%)
    'min_report_similarity' => env('PLAGIARISM_MIN_REPORT', 15),
    
    // Maximum matches to return (default: 10)
    'max_matches' => env('PLAGIARISM_MAX_MATCHES', 10),
];
```

### Environment Variables

Add to `.env` file to customize:

```env
PLAGIARISM_THRESHOLD=40
PLAGIARISM_MIN_REPORT=15
PLAGIARISM_MAX_MATCHES=10
```

## API Endpoints

### 1. Create Thesis (with plagiarism check)
```
POST /api/theses
```

**Request Body:**
```json
{
  "title": "Thesis Title",
  "abstract": "Thesis abstract...",
  "authors": ["Author Name"],
  // ... other fields
}
```

**Success Response (200):**
```json
{
  "thesis": { ... },
  "message": "Thesis created successfully",
  "plagiarism_score": 25.5,
  "plagiarism_status": "passed"
}
```

**Plagiarism Rejection (422):**
```json
{
  "message": "Thesis submission rejected due to high plagiarism detection.",
  "plagiarism_score": 45.8,
  "threshold": 40,
  "error_type": "plagiarism_detected",
  "matches": [
    {
      "thesis_id": 123,
      "thesis_title": "Similar Thesis Title",
      "similarity_score": 45.8,
      "authors": ["Author Name"]
    }
  ]
}
```

### 2. Preview Plagiarism Check
```
POST /api/theses/check-plagiarism
```

**Request Body:**
```json
{
  "title": "Thesis Title",
  "abstract": "Thesis abstract...",
  "thesis_id": null  // Optional: exclude this thesis from comparison
}
```

**Response:**
```json
{
  "score": 25.5,
  "threshold": 40,
  "status": "passed",  // or "failed"
  "message": "Plagiarism check passed. You can proceed with submission.",
  "matches": [
    {
      "thesis_id": 123,
      "thesis_title": "Similar Thesis",
      "similarity_score": 25.5
    }
  ]
}
```

## User Experience

### Upload Flow

1. **User fills in thesis form**
   - Title, abstract, authors, etc.

2. **Optional: Preview plagiarism check**
   - Click "Check Plagiarism Before Submitting"
   - See results immediately
   - Revise if needed

3. **Submit thesis**
   - Automatic plagiarism check runs
   - If score < threshold: Thesis is created
   - If score ≥ threshold: Submission rejected with details

### Success Scenario
```
✅ Thesis created successfully! (Plagiarism Score: 25.3%)
```

### Rejection Scenario
```
❌ Thesis submission rejected: Plagiarism score (45.8%) exceeds the allowed threshold of 40%.

⚠️ High Plagiarism Detected
Your thesis has a plagiarism score of 45.8%, which exceeds the maximum allowed threshold of 40%.

Similar theses found:
• "Machine Learning Applications" - 45.8% similarity
• "Deep Learning Research" - 38.2% similarity

Please revise your thesis to ensure originality before resubmitting.
```

## Database Storage

Plagiarism check results are stored in the `plagiarism_checks` table:

```sql
- thesis_id: ID of the thesis
- checked_by: User who submitted
- similarity_score: Overall plagiarism score
- matches_found: Number of similar theses
- check_result: Full JSON result with matches
- status: 'completed' or 'failed'
- created_at: Timestamp
```

## Files Modified

### Backend
1. `app/Http/Controllers/Api/ThesisController.php`
   - Added plagiarism check in `store()` method
   - Added `checkPlagiarism()` method for preview
   - Integrated with PlagiarismChecker service

2. `config/plagiarism.php` (NEW)
   - Configuration file for plagiarism settings

3. `routes/api.php`
   - Added `/theses/check-plagiarism` route

### Frontend
1. `frontend/src/pages/Theses/ThesisForm.js`
   - Added plagiarism error display
   - Added preview plagiarism check button
   - Added plagiarism preview results display
   - Enhanced error handling for plagiarism rejection

## Testing

### Test Scenarios

1. **Low Plagiarism (Pass)**
   - Submit thesis with unique content
   - Should succeed with score < 40%

2. **High Plagiarism (Reject)**
   - Submit thesis with very similar content to existing thesis
   - Should be rejected with score ≥ 40%

3. **Preview Check**
   - Fill in title and abstract
   - Click "Check Plagiarism Before Submitting"
   - Should show results without creating thesis

4. **Threshold Configuration**
   - Change `PLAGIARISM_THRESHOLD` in `.env`
   - Test with different threshold values

## Adjusting the Threshold

To change the plagiarism threshold:

1. **Option 1: Environment Variable**
   ```env
   PLAGIARISM_THRESHOLD=50  # Allow up to 50% similarity
   ```

2. **Option 2: Config File**
   Edit `backend/config/plagiarism.php`:
   ```php
   'threshold' => 50,
   ```

3. **Restart backend server** for changes to take effect

## Benefits

✅ Prevents duplicate or plagiarized content from being uploaded
✅ Maintains academic integrity
✅ Provides clear feedback to users
✅ Allows users to check before submitting
✅ Configurable threshold for different requirements
✅ Stores plagiarism history for auditing

## Notes

- Plagiarism check only runs on NEW thesis submissions
- Editing existing theses does not trigger plagiarism check
- Admin uploads still go through plagiarism check
- The check compares against approved theses only
- Similarity algorithm uses cosine similarity on tokenized text

---
**Date Implemented:** April 5, 2026
**Threshold:** 40% (configurable)
**Status:** ✅ Active
