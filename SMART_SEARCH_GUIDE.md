# Smart Search and Personalized Suggestions Guide

## Overview
The thesis system now includes intelligent search capabilities and personalized recommendations based on student programs and interests.

## Features

### 1. Smart Search
- **Autocomplete Suggestions**: As you type, get instant suggestions for:
  - Thesis titles
  - Author names
  - Keywords
  - Programs
- **Personalized Results**: Logged-in users get results prioritized by their program and department
- **Multi-field Search**: Searches across title, abstract, authors, keywords, department, and program

### 2. Personalized Suggestions
Students receive customized thesis recommendations based on:
- **Program-based**: Popular theses in your program
- **Interest-based**: Theses matching your research interests
- **Viewing History**: Similar theses to what you've viewed
- **Department**: Recent theses from your department
- **Trending**: Most viewed theses this month
- **Recently Added**: Latest additions to the library

### 3. Profile Preferences
Users can set their:
- Academic program
- Department
- Research interests (multiple keywords)

## API Endpoints

### Backend Routes

#### Public Routes
```
GET /api/search?query={query}&limit={limit}
- Smart search with personalized results for logged-in users
- Returns: results, count, personalized flag

GET /api/search/autocomplete?query={query}&limit={limit}
- Get autocomplete suggestions
- Returns: suggestions array with type and value
```

#### Protected Routes
```
GET /api/suggestions
- Get personalized suggestions for the authenticated user
- Returns: suggestions object with multiple categories

PUT /api/profile/preferences
- Update user program, department, and interests
- Body: { program, department, interests[] }
```

### Frontend Routes
```
/search-results - Display search results
/suggestions - Personalized suggestions page
/profile - Profile preferences page
```

## Database Schema

### Users Table (Updated)
```sql
- program (string, nullable)
- interests (json, nullable)
- student_id (string, nullable)
- faculty_id (string, nullable)
```

### Thesis Views Table
```sql
- id
- thesis_id (foreign key)
- user_id (foreign key, nullable)
- ip_address
- user_agent
- created_at
- updated_at
```

## Usage

### For Students

1. **Set Your Preferences**
   - Navigate to Profile (click your avatar → Profile Preferences)
   - Enter your program (e.g., "Computer Science")
   - Enter your department (e.g., "College of Engineering")
   - Add research interests (e.g., "Machine Learning", "IoT", "Web Development")
   - Click "Save Preferences"

2. **Use Smart Search**
   - Go to Browse Theses page
   - Use the smart search bar at the top
   - Start typing to see autocomplete suggestions
   - Click a suggestion or press Enter to search
   - Results are personalized based on your profile

3. **View Suggestions**
   - Click "Suggestions For You" in the sidebar
   - Browse different categories of recommendations
   - Click any thesis to view details

### For Developers

#### Adding Smart Search to a Page
```javascript
import SmartSearch from '../../components/SmartSearch/SmartSearch';

function MyPage() {
  return (
    <div>
      <SmartSearch />
      {/* Other content */}
    </div>
  );
}
```

#### Fetching Suggestions Programmatically
```javascript
import axios from 'axios';

const fetchSuggestions = async () => {
  const token = localStorage.getItem('token');
  const response = await axios.get(
    `${process.env.REACT_APP_API_URL}/suggestions`,
    { headers: { Authorization: `Bearer ${token}` } }
  );
  return response.data;
};
```

## Recommendation Algorithm

The system uses multiple factors to generate recommendations:

1. **Relevance Score**
   - Program match: 3 points
   - Department match: 2 points
   - Other: 1 point

2. **Popularity Metrics**
   - View count
   - Download count
   - Recency

3. **Content Similarity**
   - Keyword matching
   - Category overlap
   - Abstract similarity

## Testing

### Test Smart Search
1. Go to Browse Theses
2. Type "machine" in the search bar
3. Verify autocomplete suggestions appear
4. Select a suggestion and verify results

### Test Personalized Suggestions
1. Login as a student
2. Set program to "Computer Science"
3. Add interests: "AI", "Machine Learning"
4. Navigate to Suggestions For You
5. Verify recommendations appear in categories

### Test Profile Preferences
1. Login
2. Go to Profile Preferences
3. Update program, department, and interests
4. Save and verify success message
5. Check that suggestions update accordingly

## Performance Considerations

- Filter options are cached for 1 hour
- Autocomplete has 300ms debounce
- Search results limited to 20 items
- Suggestions limited to 5 items per category
- Trending calculations use 30-day window

## Future Enhancements

- Machine learning-based recommendations
- Collaborative filtering (users who viewed X also viewed Y)
- Citation network analysis
- Topic modeling for better content similarity
- Real-time trending topics
- Email notifications for new theses in your interests
