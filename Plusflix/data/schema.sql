PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS titles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  type TEXT NOT NULL CHECK (type IN ('film','serial')),
  year INTEGER NOT NULL,
  description TEXT NOT NULL,
  poster TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS platforms (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS title_category (
  title_id INTEGER NOT NULL,
  category_id INTEGER NOT NULL,
  PRIMARY KEY (title_id, category_id),
  FOREIGN KEY (title_id) REFERENCES titles(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS title_platform (
  title_id INTEGER NOT NULL,
  platform_id INTEGER NOT NULL,
  PRIMARY KEY (title_id, platform_id),
  FOREIGN KEY (title_id) REFERENCES titles(id) ON DELETE CASCADE,
  FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ratings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title_id INTEGER NOT NULL,
  client_id TEXT NOT NULL,
  value INTEGER NOT NULL CHECK (value IN (-1,1)),
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now')),
  UNIQUE(title_id, client_id),
  FOREIGN KEY (title_id) REFERENCES titles(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_ratings_title ON ratings(title_id);

CREATE TABLE IF NOT EXISTS comments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title_id INTEGER NOT NULL,
  content TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (title_id) REFERENCES titles(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_titles_year ON titles(year);
CREATE INDEX IF NOT EXISTS idx_titles_type ON titles(type);
CREATE INDEX IF NOT EXISTS idx_tc_cat ON title_category(category_id);
CREATE INDEX IF NOT EXISTS idx_tp_plat ON title_platform(platform_id);
