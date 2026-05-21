# 📋 Project Plan: Pokémon Collection Tracker

## 🎯 Concept
**Recommended Choice**  
Users build their own Pokémon collection. This acts as a **Pokédex + Collection Manager**.

**Core Capabilities:**
- Add Pokémon
- Upload images
- Assign types
- Rate favorites
- Track caught status
- Organize collections

> 💡 *Highly scoped and very doable within a 2-week timeframe.*

---

## ✨ Feature Breakdown

### 👤 User System
- [ ] Register / Login / Logout
- [ ] Session handling

### 🐾 Pokémon CRUD
- [ ] Add Pokémon
- [ ] Edit Pokémon
- [ ] Delete Pokémon
- [ ] View Pokémon

### 🖼️ File Upload
- [ ] Upload Pokémon image

### 📊 Dashboard
- [ ] Total Pokémon caught counter
- [ ] Favorite Pokémon display
- [ ] Stats/Cards overview

### 🔍 Filtering
- [ ] Filter by type
- [ ] Filter by status

---

## 🗄️ Database Schema

### `users`
| id | username | password |
|----|----------|----------|

### `pokemon`
| id | user_id | name | type | level | status | image |
|----|---------|------|------|-------|--------|-------|

---

## ✅ Requirements Coverage
This project satisfies all core technical requirements:
- ✅ Related tables & Foreign Keys
- ✅ Full CRUD operations
- ✅ `JOIN` & `WHERE` queries
- ✅ Session management
- ✅ Input validation
- ✅ File uploads
