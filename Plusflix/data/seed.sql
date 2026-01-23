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



 -- Arbitralnie wstawiony rating --
INSERT INTO ratings (title_id, client_id, value) VALUES
                                                     (1, 'seed_user_1', 1),
                                                     (1, 'seed_user_2', 1),
                                                     (1, 'seed_user_3', 1),
                                                     (1, 'seed_user_4', 1),
                                                     (1, 'seed_user_5', -1),
                                                     (1, 'seed_user_6', 1),
                                                     (1, 'seed_user_7', -1),
                                                     (1, 'seed_user_8', 1),
                                                     (1, 'seed_user_9', -1),
                                                     (1, 'seed_user_11', -1),
                                                     (1, 'seed_user_12', 1),
                                                     (1, 'seed_user_13', 1),

                                                     (2, 'seed_user_1', -1),
                                                     (2, 'seed_user_2', -1),
                                                     (2, 'seed_user_3', 1),
                                                     (2, 'seed_user_4', -1),
                                                     (2, 'seed_user_5', 1),
                                                     (2, 'seed_user_6', -1),
                                                     (2, 'seed_user_7', -1),
                                                     (2, 'seed_user_8', 1),
                                                     (2, 'seed_user_9', 1),

                                                     (3, 'seed_user_1', 1),
                                                     (3, 'seed_user_2', 1),
                                                     (3, 'seed_user_3', -1),
                                                     (3, 'seed_user_4', 1),
                                                     (3, 'seed_user_5', 1),
                                                     (3, 'seed_user_6', -1),
                                                     (3, 'seed_user_7', 1);
