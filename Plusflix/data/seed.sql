PRAGMA foreign_keys = ON;

INSERT OR IGNORE INTO categories(name) VALUES
 ('Sci-Fi'),('Thriller'),('Drama');

INSERT OR IGNORE INTO platforms(name) VALUES
 ('Netflix'),('Disney+'),('Apple TV+'),('Canal+');

INSERT INTO titles(name,type,year,description,poster) VALUES
 ('Inception','film',2010,'Złodziej wchodzi do snów, by ukraść sekrety — i dostać ostatnie zlecenie.','inception.jpg'),
 ('Snowpiercer','serial',2020,'Ocalała ludzkość żyje w pociągu pędzącym przez zamarznięty świat.','snowpiercer.jpg'),
 ('Interstellar','film',2014,'Misja ratunkowa w kosmosie w poszukiwaniu nowego domu dla ludzkości.','interstellar.jpg');

-- powiązania kategorie
INSERT INTO title_category(title_id, category_id)
SELECT 1, id FROM categories WHERE name='Sci-Fi';
INSERT INTO title_category(title_id, category_id)
SELECT 1, id FROM categories WHERE name='Thriller';

INSERT INTO title_category(title_id, category_id)
SELECT 2, id FROM categories WHERE name='Sci-Fi';
INSERT INTO title_category(title_id, category_id)
SELECT 2, id FROM categories WHERE name='Drama';

INSERT INTO title_category(title_id, category_id)
SELECT 3, id FROM categories WHERE name='Sci-Fi';
INSERT INTO title_category(title_id, category_id)
SELECT 3, id FROM categories WHERE name='Drama';

-- powiązania platformy
INSERT INTO title_platform(title_id, platform_id)
SELECT 1, id FROM platforms WHERE name='Netflix';
INSERT INTO title_platform(title_id, platform_id)
SELECT 1, id FROM platforms WHERE name='Apple TV+';

INSERT INTO title_platform(title_id, platform_id)
SELECT 2, id FROM platforms WHERE name='Netflix';

INSERT INTO title_platform(title_id, platform_id)
SELECT 3, id FROM platforms WHERE name='Disney+';
