import sqlite3
import os

def connect_db():
    if not os.path.exists('mydatabase.db'):
        conn = sqlite3.connect('mydatabase.db')
        init_database(conn)
    else :
        conn = sqlite3.connect('mydatabase.db')
    return conn


def init_database(conn):
    cursor = conn.cursor()

    cursor.execute('''CREATE TABLE articles(
                        id SERIAL, 
                        id_arxiv TEXT NOT NULL,
                        title TEXT,
                        publication_date TEXT,
                        author TEXT,
                        abstract TEXT,
                        summary TEXT,
                        link TEXT,
                        PRIMARY KEY (id, id_arxiv)
                   )''')
    cursor.execute("""INSERT INTO articles (id, id_arxiv, title, publication_date, author, abstract, summary, link) VALUES
                        (1, '2406.00020', 'Harmful Speech Detection by Language Models Exhibits Gender-Queer Dialect Bias', '2024-05-23', 'Rebecca Dorn, Lee Kezar, Fred Morstatter, Kristina Lerman', "La modération de contenu sur les plateformes de médias sociaux influence la dynamique du discours en ligne, affectant quelles voix sont amplifiées ou supprimées. Cette étude examine les biais dans la classification des discours nuisibles concernant les dialectes gender-queer en ligne, en se concentrant sur l\'utilisation de termes réappropriés. Un nouveau jeu de données, QueerReclaimLex, est introduit, basé sur 109 modèles illustrant des usages non péjoratifs de termes LGBTQ+. Les performances de cinq modèles de langage sont évaluées pour détecter les biais potentiels.", 'Cette étude révèle que les modèles de langage actuels ont tendance à classer à tort les textes rédigés par des individus gender-queer comme nuisibles, soulignant la nécessité de pratiques de modération de contenu plus équitables et inclusives.', 'https://arxiv.org/abs/2406.00020'),
                        (2, '2012.12305', 'Confronting Abusive Language Online: A Survey from the Ethical and Human Rights Perspective', '2020-12-22', 'Svetlana Kiritchenko, Isar Nejadgholi, Kathleen C. Fraser', "La prolifération de contenus abusifs sur Internet peut entraîner des dommages psychologiques et physiques graves. Cet article passe en revue les recherches en traitement automatique des langues sur la détection de contenus abusifs, en se concentrant sur les défis éthiques liés à la confidentialité, la responsabilité, la transparence, l\'équité et la non-discrimination. Les auteurs soulignent la nécessité d\'examiner les impacts sociaux de ces technologies et d\'intégrer des considérations éthiques à chaque étape de leur développement.", "Cette enquête met en évidence l\'importance d\'aborder les défis éthiques dans la détection automatique des discours abusifs en ligne, en proposant des solutions socio-techniques pour créer des espaces en ligne plus inclusifs.", 'https://arxiv.org/abs/2012.12305'),
                        (3, '2106.00742', 'A Systematic Review of Hate Speech Automatic Detection Using Natural Language Processing', '2021-05-22', 'Md Saroar Jahan, Mourad Oussalah', "Avec la multiplication des plateformes de médias sociaux offrant l\'anonymat et un accès facile, la détection et le suivi des discours de haine deviennent un défi croissant pour la société. Cet article fournit une revue systématique des travaux de recherche sur la détection automatique des discours de haine, en mettant l\'accent sur les technologies de traitement automatique des langues et d\'apprentissage profond. Les auteurs discutent des ressources existantes, des méthodes employées et des directions futures pour améliorer les performances des systèmes de détection.", 'Cette revue systématique analyse les approches actuelles de détection automatique des discours de haine, en soulignant les limitations des méthodes existantes et en suggérant des orientations pour de futures recherches.', 'https://arxiv.org/abs/2106.00742'),
                        (4, '2103.00153', 'Detecting Harmful Content On Online Platforms: What Platforms Need Vs. Where Research Efforts Go', '2021-02-27', 'Arnav Arora, Preslav Nakov, Momchil Hardalov, Sheikh Muhammad Sarwar, Vibha Nayak, Yoan Dinkov, Dimitrina Zlatkova, Kyle Dent, Ameya Bhatawdekar, Guillaume Bouchard, Isabelle Augenstein', 'La prolifération de contenus nuisibles sur les plateformes en ligne est un problème sociétal majeur, se manifestant sous diverses formes telles que les discours de haine, la désinformation et le harcèlement. Cet article examine le fossé entre les besoins des plateformes en matière de détection de contenus nuisibles et les efforts de recherche actuels. Les auteurs passent en revue les politiques de modération de contenu et les méthodes de détection automatique, proposant des directions pour aligner les efforts de recherche sur les besoins pratiques des plateformes.', 'Cet article souligne la divergence entre les besoins des plateformes en ligne pour détecter les contenus nuisibles et les efforts de recherche actuels, suggérant des pistes pour harmoniser les deux.', 'https://arxiv.org/abs/2103.00153'),
                        (5, '2110.05287v1', 'TEET! Tunisian Dataset for Toxic Speech Detection', '2021-10-11', 'Slim Gharbi, Heger Arfaoui, Hatem Haddad, Mayssa Kchaou', "La liberté d\'expression sur les réseaux sociaux a conduit à la propagation de contenus nuisibles et abusifs. Cet article présente un nouveau jeu de données annoté, composé d\'environ 10 000 commentaires en dialecte tunisien, visant à détecter les discours toxiques. Les auteurs explorent diverses méthodes de représentation vectorielle et comparent plusieurs classificateurs d\'apprentissage automatique et de modèles d\'apprentissage profond pour améliorer l\'efficacité de la détection.", "Les auteurs introduisent un jeu de données spécifique au dialecte tunisien pour la détection de discours toxiques et évaluent différentes approches d\'apprentissage automatique pour cette tâche.", 'https://arxiv.org/abs/2110.05287v1');
                    """)

    conn.commit()

def close_db(conn):
    conn.close()


def select_summary(conn, id_arxiv):
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM articles WHERE id_arxiv = ?", (id_arxiv,))
    data = cursor.fetchall()
    return data


def insert_article(conn, article):
    print(article)
    cursor = conn.cursor()
    cursor.execute("INSERT INTO articles (id_arxiv, title, publication_date, author, abstract, summary, link) VALUES (?, ?, ?, ?, ?, ?, ?)", 
                    (article['arxivId'], article['title'], article['date'], article['authors'], article['abstract'], article['summary'], article['link']))
    conn.commit()
    return cursor.lastrowid