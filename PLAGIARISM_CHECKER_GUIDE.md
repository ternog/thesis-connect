# Plagiarism Checker Guide

## Overview
The plagiarism checker helps detect similarities between thesis submissions and existing theses in the database using text similarity algorithms.

## Features

### 1. Text-Based Similarity Detection
- Uses cosine similarity algorithm
- Compares title and abstract content
- Filters out common stop words
- Provides percentage-based similarity scores

### 2. Severity Levels
- **High (70%+)**: Significant similarity - requires major revision
- **Medium (40-69%)**: Moderate similarity - review and revise
- **Low (20-39%)**: Minor similarities - acceptable
- **Minimal (<20%)**: Original content

### 3. Detailed Match Reports
- Lists all similar theses found
- Shows individual similarity scores
- Links to original theses for comparison
- Tracks check history

## Setup

### 1. Run Migration
```bash
cd thesis-system/backend
php artisan migrate
```

This creates the `plagiarism_checks` table.

### 2. Clear Caches
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### 3. Restart Server
```bash
php artisan serve
```

## Usage

### For Students

1. **Before Submission**
   - Navigate to "Plagiarism Checker" in the sidebar
   - Paste your thesis title and abstract (minimum 100 characters)
   - Click "Check for Plagiarism"
   - Review the similarity score and matches
   - Revise content if similarity is too high

2. **Understanding Results**
   - Green (Minimal): Your content is original
   - Yellow (Low): Minor similarities, acceptable
   - Orange (Medium): Review and revise similar sections
   - Red (High): Significant revision required

### For Faculty/Admin

1. **Check Submitted Thesis**
   - Go to thesis detail page
   - Click "Check Plagiarism" button
   - View detailed report with all matches
   - Review similarity scores

2. **View Check History**
   - Navigate to thesis plagiarism history
   - See all previous checks
   - Compare scores over time
   - Track revisions

## API Endpoints

### Check Text Content
```
POST /api/plagiarism/check-text
Body: {
  "text": "Your thesis content here...",
  "thesis_id": 123 (optional, to exclude from comparison)
}
```

### Check Specific Thesis
```
POST /api/theses/{id}/plagiarism-check
```

### Get Thesis Check History
```
GET /api/theses/{id}/plagiarism-checks
```

### Get All Checks (Admin)
```
GET /api/plagiarism-checks?severity=high
```

### Get Check Details
```
GET /api/plagiarism-checks/{checkId}
```

## How It Works

### Algorithm

1. **Text Preprocessing**
   - Convert to lowercase
   - Remove special characters
   - Tokenize into words
   - Remove stop words (the, a, an, etc.)
   - Filter short words (< 3 characters)

2. **Similarity Calculation**
   - Create frequency vectors for both texts
   - Calculate cosine similarity
   - Convert to percentage (0-100%)

3. **Matching**
   - Compare against all approved theses
   - Exclude the thesis being checked
   - Return matches above 15% threshold
   - Sort by similarity score

### Example

**Input Text:**
```
"Machine Learning Applications in Healthcare Data Analysis"
```

**Comparison:**
```
Existing Thesis: "Deep Learning for Medical Data Processing"
Similarity: 45% (Medium)
```

## Database Schema

### plagiarism_checks Table
```sql
- id (primary key)
- thesis_id (foreign key to theses)
- checked_by (foreign key to users)
- similarity_score (decimal 0-100)
- matches (JSON array of matching theses)
- checked_content (text sample)
- status (pending/completed/failed)
- notes (text)
- created_at
- updated_at
```

## Best Practices

### For Students
1. Run check before final submission
2. Aim for similarity score below 20%
3. Properly cite referenced work
4. Paraphrase instead of copying
5. Use original research and analysis

### For Faculty
1. Check all submissions before approval
2. Review high-similarity matches manually
3. Provide feedback on similar sections
4. Track revision improvements
5. Document check results

## Limitations

1. **Text-Only Analysis**
   - Only compares title and abstract
   - Does not analyze full PDF content
   - Cannot detect paraphrased content

2. **Database Scope**
   - Only compares against theses in the system
   - Does not check external sources
   - Limited to approved theses

3. **Algorithm Limitations**
   - May flag common terminology
   - Cannot detect intentional plagiarism
   - Requires manual review for context

## Future Enhancements

1. **PDF Content Analysis**
   - Extract and analyze full thesis text
   - Compare entire documents
   - Section-by-section comparison

2. **External Source Checking**
   - Integration with online plagiarism APIs
   - Web search for similar content
   - Academic database integration

3. **Advanced Algorithms**
   - Semantic similarity analysis
   - Citation network analysis
   - Machine learning-based detection

4. **Automated Reporting**
   - Generate detailed PDF reports
   - Email notifications for high similarity
   - Automated revision tracking

## Troubleshooting

### Check Not Running
- Ensure backend server is running
- Check user has proper permissions
- Verify database migration completed
- Check Laravel logs for errors

### Low Accuracy
- Ensure sufficient text length (100+ chars)
- Check database has approved theses
- Verify text preprocessing is working
- Review algorithm parameters

### Performance Issues
- Index database tables properly
- Limit comparison to recent theses
- Cache frequently accessed data
- Optimize query performance

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for frontend errors
3. Verify API endpoints are accessible
4. Test with sample data first
