# This python script will created a new table for traditional Chinese and Bopomofo.
# You should install firt pip install opencc-python-reimplemented
# You can run (python is python3)
# python update_mariadb_strokes_hexstrokes-separator-full-trad-bopo.py --traditional
# to get the traditional database spip_demoindexzh_trad instead of spip_demoindex
# The new table spip_demoindexzh_trad or  spip_demoindexzh is erased and
# created from spip_demoindex.
# The spip_demoindex has a chinese 'terme' column which has been entered
# from the wiki in simplifed Chinese.
# The output new table includes new columns in order to get
# the indexes sorted in different ways, by Pinyin, by Bopomofo (you need
# to have the flag --traditional because the simplified is not known by Bopomofo
# readers.
# Nicolas Brouard brouard@ined.fr under the gpl license July 2025

import re
import pymysql
import pymysql.cursors
#import unidecode

import argparse

parser = argparse.ArgumentParser(description='Generate demoindex data for a given edition.')
parser.add_argument('--traditional', action='store_true', help='Convert terms to Traditional Chinese')
parser.add_argument('--edition', required=True, help='Language edition code, e.g., zh-ii')

args = parser.parse_args()

edition = args.edition
use_traditional = args.traditional

print(f"Processing edition: {edition}")
if use_traditional:
    from opencc import OpenCC
    cc_s2t = OpenCC('s2t')
    print("Conversion to Traditional Chinese will be applied.")


# Database connection details
db_config = {
    "unix_socket": "/var/lib/mysql/mysql.sock",  # Change if needed
    "user": "root",  # Replace with your username
    "password": "ticsympa",  # Replace with your password
    "database": "tools",
    "charset": "utf8mb4",
    "cursorclass":pymysql.cursors.DictCursor
}

# def add_column_if_not_exists(cursor, table_name, column_name, column_def):
#     cursor.execute(f"SHOW COLUMNS FROM `{table_name}` LIKE %s", (column_name,))
#     result = cursor.fetchone()
#     if not result:
#         cursor.execute(f"ALTER TABLE `{table_name}` ADD COLUMN {column_name} {column_def}")

# Connect to database
conn = pymysql.connect(**db_config)
cursor = conn.cursor()
#if args.traditional:
#    tabledemo = "spip_demoindexzh_trad"
#else:
#    tabledemo = "spip_demoindexzh" # meaning spimplified
# or
# Choose table
tabledemo = "spip_demoindexzh_trad" if args.traditional else "spip_demoindexzh"
    
# Drop and recreate the new table
#mysql -u root --password=ticsympa tools -e "DROP TABLE IF EXISTS spip_demoindexzh_trad;"
#mysql -u root --password=ticsympa tools -e "CREATE TABLE spip_demoindexzh_trad LIKE spip_demoindex;"
#mysql -u root --password=ticsympa tools -e "SELECT * FROM spip_demoindexzh_trad LIMIT 20;"

cursor.execute(f"DROP TABLE IF EXISTS `{tabledemo}`")
cursor.execute(f"CREATE TABLE `{tabledemo}` LIKE spip_demoindex")
cursor.execute(f"ALTER TABLE `{tabledemo}`  MODIFY termezh LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
cursor.execute(f"ALTER TABLE `{tabledemo}` DROP COLUMN IF EXISTS termeth")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_pinyin VARCHAR(255)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_diacrpinyin VARCHAR(255)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_firstchar_pinyin CHAR(1)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_strokes INT(11)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_HEX_strokes VARCHAR(255)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS first_english CHAR(1)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_strokes_separator VARCHAR(10)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_bopomofo VARCHAR(255)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_firstchar_bopomofo VARCHAR(10)")
cursor.execute(f"ALTER TABLE `{tabledemo}` ADD COLUMN IF NOT EXISTS termezh_bopomofo_sort_key VARCHAR(255)")

#####

# Replacing 十六画  16 strokes by something else:
    # 非汉字 (Fēi Hànzì) - "Non-Chinese Characters"
    # This is clear and straightforward, indicating that the characters in this group are not Chinese.

    # 其他 (Qítā) - "Other"
    # A simple and neutral term that groups all non-Chinese characters together.

    # 未分类 (Wèi Fēnlèi) - "Unclassified"
    # This implies that the characters don't fit into the existing stroke-based categories.

    # 非笔画字符 (Fēi Bǐhuà Zìfú) - "Non-Stroke Characters"
    # This emphasizes that the characters are not sorted by stroke count.

    # 外文字符 (Wàiwén Zìfú) - "Foreign Characters"
    # This highlights that the characters are from non-Chinese writing systems.

    # 符号与字母 (Fúhào yǔ Zìmǔ) - "Symbols and Letters"
    # This is more descriptive and specifies the types of characters in this group.
if args.traditional:
    stroke_labels = {
        1: "一画", 2: "二画", 3: "三画", 4: "四画", 5: "五画",
        6: "六画", 7: "七画", 8: "八画", 9: "九画", 10: "十画",
        11: "十一画", 12: "十二画", 13: "十三画", 14: "十四画",
        15: "十五画", 16: "十六画", 17: "十七画", 18: "十八画",
        19: "十九画", 20: "二十画", 21: "二十一画", 22: "二十二画",
        23: "二十三画", 24: "二十四画", 25: "二十五画", 26: "二十六画",
        27: "二十七画", 28: "二十八画", 29: "二十九画", 30: "三十画"
    }
else:
    stroke_labels = {
        1: "一层", 2: "二画", 3: "三画", 4: "四画", 5: "五画",
        6: "六画", 7: "七画", 8: "八画", 9: "九画", 10: "十画",
        11: "十一画", 12: "十二画", 13: "十三画", 14: "十四画",
        15: "十五画", 16: "十六画", 17: "十七画", 18: "十八画",
        19: "十九画", 20: "二十画", 21: "二十一画", 22: "二十二画",
        23: "二十三画", 24: "二十四画", 25: "二十五画", 26: "二十六画",
        27: "二十七画", 28: "二十八画", 29: "二十九画", 30: "三十画"
    }

# Paths to Unihan files
unihan_readings = "/var/www/html/demopaediahead/demopaedia-mw28/html/tools/plugins/auto/demopaedia/inc/Unihan/Unihan_Readings.txt"
unihan_irgsources = "/var/www/html/demopaediahead/demopaedia-mw28/html/tools/plugins/auto/demopaedia/inc/Unihan/Unihan_IRGSources.txt"

# bopomofo
pinyin_to_bopomofo = {
    "a": "ㄚ",
    "ai": "ㄞ",
    "an": "ㄢ",
    "ang": "ㄤ",
    "ao": "ㄠ",
    "ba": "ㄅㄚ",
    "bai": "ㄅㄞ",
    "ban": "ㄅㄢ",
    "bang": "ㄅㄤ",
    "bao": "ㄅㄠ",
    "bei": "ㄅㄟ",
    "ben": "ㄅㄣ",
    "beng": "ㄅㄥ",
    "bi": "ㄅㄧ",
    "bian": "ㄅㄧㄢ",
    "biao": "ㄅㄧㄠ",
    "bie": "ㄅㄧㄝ",
    "bin": "ㄅㄧㄣ",
    "bing": "ㄅㄧㄥ",
    "bo": "ㄅㄛ",
    "bu": "ㄅㄨ",
    "ca": "ㄘㄚ",
    "cai": "ㄘㄞ",
    "can": "ㄘㄢ",
    "cang": "ㄘㄤ",
    "cao": "ㄘㄠ",
    "ce": "ㄘㄜ",
    "cen": "ㄘㄣ",
    "ceng": "ㄘㄥ",
    "cha": "ㄔㄚ",
    "chai": "ㄔㄞ",
    "chan": "ㄔㄢ",
    "chang": "ㄔㄤ",
    "chao": "ㄔㄠ",
    "che": "ㄔㄜ",
    "chen": "ㄔㄣ",
    "cheng": "ㄔㄥ",
    "chi": "ㄔ",
    "chong": "ㄔㄨㄥ",
    "chou": "ㄔㄡ",
    "chu": "ㄔㄨ",
    "chua": "ㄔㄨㄚ",
    "chuai": "ㄔㄨㄞ",
    "chuan": "ㄔㄨㄢ",
    "chuang": "ㄔㄨㄤ",
    "chui": "ㄔㄨㄟ",
    "chun": "ㄔㄨㄣ",
    "chuo": "ㄔㄨㄛ",
    "ci": "ㄘ",
    "cong": "ㄘㄨㄥ",
    "cou": "ㄘㄡ",
    "cu": "ㄘㄨ",
    "cuan": "ㄘㄨㄢ",
    "cui": "ㄘㄨㄟ",
    "cun": "ㄘㄨㄣ",
    "cuo": "ㄘㄨㄛ",
    "da": "ㄉㄚ",
    "dai": "ㄉㄞ",
    "dan": "ㄉㄢ",
    "dang": "ㄉㄤ",
    "dao": "ㄉㄠ",
    "de": "ㄉㄜ",
    "dei": "ㄉㄟ",
    "deng": "ㄉㄥ",
    "di": "ㄉㄧ",
    "dian": "ㄉㄧㄢ",
    "diao": "ㄉㄧㄠ",
    "die": "ㄉㄧㄝ",
    "ding": "ㄉㄧㄥ",
    "diu": "ㄉㄧㄡ",
    "dong": "ㄉㄨㄥ",
    "dou": "ㄉㄡ",
    "du": "ㄉㄨ",
    "duan": "ㄉㄨㄢ",
    "dui": "ㄉㄨㄟ",
    "dun": "ㄉㄨㄣ",
    "duo": "ㄉㄨㄛ",
    "e": "ㄜ",
    "ei": "ㄟ",
    "en": "ㄣ",
    "eng": "ㄥ",
    "er": "ㄦ",
    "fa": "ㄈㄚ",
    "fan": "ㄈㄢ",
    "fang": "ㄈㄤ",
    "fei": "ㄈㄟ",
    "fen": "ㄈㄣ",
    "feng": "ㄈㄥ",
    "fo": "ㄈㄛ",
    "fou": "ㄈㄡ",
    "fu": "ㄈㄨ",
    "ga": "ㄍㄚ",
    "gai": "ㄍㄞ",
    "gan": "ㄍㄢ",
    "gang": "ㄍㄤ",
    "gao": "ㄍㄠ",
    "ge": "ㄍㄜ",
    "gei": "ㄍㄟ",
    "gen": "ㄍㄣ",
    "geng": "ㄍㄥ",
    "gong": "ㄍㄨㄥ",
    "gou": "ㄍㄡ",
    "gu": "ㄍㄨ",
    "gua": "ㄍㄨㄚ",
    "guai": "ㄍㄨㄞ",
    "guan": "ㄍㄨㄢ",
    "guang": "ㄍㄨㄤ",
    "gui": "ㄍㄨㄟ",
    "gun": "ㄍㄨㄣ",
    "guo": "ㄍㄨㄛ",
    "ha": "ㄏㄚ",
    "hai": "ㄏㄞ",
    "han": "ㄏㄢ",
    "hang": "ㄏㄤ",
    "hao": "ㄏㄠ",
    "he": "ㄏㄜ",
    "hei": "ㄏㄟ",
    "hen": "ㄏㄣ",
    "heng": "ㄏㄥ",
    "hong": "ㄏㄨㄥ",
    "hou": "ㄏㄡ",
    "hu": "ㄏㄨ",
    "hua": "ㄏㄨㄚ",
    "huai": "ㄏㄨㄞ",
    "huan": "ㄏㄨㄢ",
    "huang": "ㄏㄨㄤ",
    "hui": "ㄏㄨㄟ",
    "hun": "ㄏㄨㄣ",
    "huo": "ㄏㄨㄛ",
    "ji": "ㄐㄧ",
    "jia": "ㄐㄧㄚ",
    "jian": "ㄐㄧㄢ",
    "jiang": "ㄐㄧㄤ",
    "jiao": "ㄐㄧㄠ",
    "jie": "ㄐㄧㄝ",
    "jin": "ㄐㄧㄣ",
    "jing": "ㄐㄧㄥ",
    "jiong": "ㄐㄩㄥ",
    "jiu": "ㄐㄧㄡ",
    "ju": "ㄐㄩ",
    "juan": "ㄐㄩㄢ",
    "jue": "ㄐㄩㄝ",
    "jun": "ㄐㄩㄣ",
    "ka": "ㄎㄚ",
    "kai": "ㄎㄞ",
    "kan": "ㄎㄢ",
    "kang": "ㄎㄤ",
    "kao": "ㄎㄠ",
    "ke": "ㄎㄜ",
    "ken": "ㄎㄣ",
    "keng": "ㄎㄥ",
    "kong": "ㄎㄨㄥ",
    "kou": "ㄎㄡ",
    "ku": "ㄎㄨ",
    "kua": "ㄎㄨㄚ",
    "kuai": "ㄎㄨㄞ",
    "kuan": "ㄎㄨㄢ",
    "kuang": "ㄎㄨㄤ",
    "kui": "ㄎㄨㄟ",
    "kun": "ㄎㄨㄣ",
    "kuo": "ㄎㄨㄛ",
    "la": "ㄌㄚ",
    "lai": "ㄌㄞ",
    "lan": "ㄌㄢ",
    "lang": "ㄌㄤ",
    "lao": "ㄌㄠ",
    "le": "ㄌㄜ",
    "lei": "ㄌㄟ",
    "leng": "ㄌㄥ",
    "li": "ㄌㄧ",
    "lia": "ㄌㄧㄚ",
    "lian": "ㄌㄧㄢ",
    "liang": "ㄌㄧㄤ",
    "liao": "ㄌㄧㄠ",
    "lie": "ㄌㄧㄝ",
    "lin": "ㄌㄧㄣ",
    "ling": "ㄌㄧㄥ",
    "liu": "ㄌㄧㄡ",
    "lo": "ㄌㄛ",
    "long": "ㄌㄨㄥ",
    "lou": "ㄌㄡ",
    "lu": "ㄌㄨ",
    "luan": "ㄌㄨㄢ",
    "lun": "ㄌㄨㄣ",
    "luo": "ㄌㄨㄛ",
    "lü": "ㄌㄩ",
    "lüan": "ㄌㄩㄢ",
    "lüe": "ㄌㄩㄝ",
    "ma": "ㄇㄚ",
    "mai": "ㄇㄞ",
    "man": "ㄇㄢ",
    "mang": "ㄇㄤ",
    "mao": "ㄇㄠ",
    "me": "ㄇㄜ",
    "mei": "ㄇㄟ",
    "men": "ㄇㄣ",
    "meng": "ㄇㄥ",
    "mi": "ㄇㄧ",
    "mian": "ㄇㄧㄢ",
    "miao": "ㄇㄧㄠ",
    "mie": "ㄇㄧㄝ",
    "min": "ㄇㄧㄣ",
    "ming": "ㄇㄧㄥ",
    "miu": "ㄇㄧㄡ",
    "mo": "ㄇㄛ",
    "mou": "ㄇㄡ",
    "mu": "ㄇㄨ",
    "na": "ㄋㄚ",
    "nai": "ㄋㄞ",
    "nan": "ㄋㄢ",
    "nang": "ㄋㄤ",
    "nao": "ㄋㄠ",
    "ne": "ㄋㄜ",
    "nei": "ㄋㄟ",
    "nen": "ㄋㄣ",
    "neng": "ㄋㄥ",
    "ni": "ㄋㄧ",
    "nian": "ㄋㄧㄢ",
    "niang": "ㄋㄧㄤ",
    "niao": "ㄋㄧㄠ",
    "nie": "ㄋㄧㄝ",
    "nin": "ㄋㄧㄣ",
    "ning": "ㄋㄧㄥ",
    "niu": "ㄋㄧㄡ",
    "nong": "ㄋㄨㄥ",
    "nou": "ㄋㄡ",
    "nu": "ㄋㄨ",
    "nuan": "ㄋㄨㄢ",
    "nun": "ㄋㄨㄣ",
    "nuo": "ㄋㄨㄛ",
    "nü": "ㄋㄩ",
    "nüe": "ㄋㄩㄝ",
    "o": "ㄛ",
    "ou": "ㄡ",
    "pa": "ㄆㄚ",
    "pai": "ㄆㄞ",
    "pan": "ㄆㄢ",
    "pang": "ㄆㄤ",
    "pao": "ㄆㄠ",
    "pei": "ㄆㄟ",
    "pen": "ㄆㄣ",
    "peng": "ㄆㄥ",
    "pi": "ㄆㄧ",
    "pia": "ㄆㄧㄚ",
    "pian": "ㄆㄧㄢ",
    "piao": "ㄆㄧㄠ",
    "pie": "ㄆㄧㄝ",
    "pin": "ㄆㄧㄣ",
    "ping": "ㄆㄧㄥ",
    "po": "ㄆㄛ",
    "pou": "ㄆㄡ",
    "pu": "ㄆㄨ",
    "q": "ㄑ",
    "qi": "ㄑㄧ",
    "qia": "ㄑㄧㄚ",
    "qian": "ㄑㄧㄢ",
    "qiang": "ㄑㄧㄤ",
    "qiao": "ㄑㄧㄠ",
    "qie": "ㄑㄧㄝ",
    "qin": "ㄑㄧㄣ",
    "qing": "ㄑㄧㄥ",
    "qiong": "ㄑㄩㄥ",
    "qiu": "ㄑㄧㄡ",
    "qu": "ㄑㄩ",
    "quan": "ㄑㄩㄢ",
    "que": "ㄑㄩㄝ",
    "qun": "ㄑㄩㄣ",
    "ran": "ㄖㄢ",
    "rang": "ㄖㄤ",
    "rao": "ㄖㄠ",
    "re": "ㄖㄜ",
    "ren": "ㄖㄣ",
    "reng": "ㄖㄥ",
    "ri": "ㄖ",
    "rong": "ㄖㄨㄥ",
    "rou": "ㄖㄡ",
    "ru": "ㄖㄨ",
    "ruan": "ㄖㄨㄢ",
    "rui": "ㄖㄨㄟ",
    "run": "ㄖㄨㄣ",
    "ruo": "ㄖㄨㄛ",
    "sa": "ㄙㄚ",
    "sai": "ㄙㄞ",
    "san": "ㄙㄢ",
    "sang": "ㄙㄤ",
    "sao": "ㄙㄠ",
    "se": "ㄙㄜ",
    "sei": "ㄙㄟ",
    "sen": "ㄙㄣ",
    "seng": "ㄙㄥ",
    "sha": "ㄕㄚ",
    "shai": "ㄕㄞ",
    "shan": "ㄕㄢ",
    "shang": "ㄕㄤ",
    "shao": "ㄕㄠ",
    "she": "ㄕㄜ",
    "shei": "ㄕㄟ",
    "shen": "ㄕㄣ",
    "sheng": "ㄕㄥ",
    "shi": "ㄕ",
    "shou": "ㄕㄡ",
    "shu": "ㄕㄨ",
    "shua": "ㄕㄨㄚ",
    "shuai": "ㄕㄨㄞ",
    "shuan": "ㄕㄨㄢ",
    "shuang": "ㄕㄨㄤ",
    "shui": "ㄕㄨㄟ",
    "shun": "ㄕㄨㄣ",
    "shuo": "ㄕㄨㄛ",
    "si": "ㄙ",
    "song": "ㄙㄨㄥ",
    "sou": "ㄙㄡ",
    "su": "ㄙㄨ",
    "suan": "ㄙㄨㄢ",
    "sui": "ㄙㄨㄟ",
    "sun": "ㄙㄨㄣ",
    "suo": "ㄙㄨㄛ",
    "ta": "ㄊㄚ",
    "tai": "ㄊㄞ",
    "tan": "ㄊㄢ",
    "tang": "ㄊㄤ",
    "tao": "ㄊㄠ",
    "te": "ㄊㄜ",
    "teng": "ㄊㄥ",
    "ti": "ㄊㄧ",
    "tian": "ㄊㄧㄢ",
    "tiao": "ㄊㄧㄠ",
    "tie": "ㄊㄧㄝ",
    "ting": "ㄊㄧㄥ",
    "tong": "ㄊㄨㄥ",
    "tou": "ㄊㄡ",
    "tu": "ㄊㄨ",
    "tuan": "ㄊㄨㄢ",
    "tui": "ㄊㄨㄟ",
    "tun": "ㄊㄨㄣ",
    "tuo": "ㄊㄨㄛ",
    "wa": "ㄨㄚ",
    "wai": "ㄨㄞ",
    "wan": "ㄨㄢ",
    "wang": "ㄨㄤ",
    "wei": "ㄨㄟ",
    "wen": "ㄨㄣ",
    "weng": "ㄨㄥ",
    "wo": "ㄨㄛ",
    "wu": "ㄨ",
    "xi": "ㄒㄧ",
    "xia": "ㄒㄧㄚ",
    "xian": "ㄒㄧㄢ",
    "xiang": "ㄒㄧㄤ",
    "xiao": "ㄒㄧㄠ",
    "xie": "ㄒㄧㄝ",
    "xin": "ㄒㄧㄣ",
    "xing": "ㄒㄧㄥ",
    "xiong": "ㄒㄩㄥ",
    "xiu": "ㄒㄧㄡ",
    "xu": "ㄒㄩ",
    "xuan": "ㄒㄩㄢ",
    "xue": "ㄒㄩㄝ",
    "xun": "ㄒㄩㄣ",
    "ya": "ㄧㄚ",
    "yai": "ㄧㄞ",
    "yan": "ㄧㄢ",
    "yang": "ㄧㄤ",
    "yao": "ㄧㄠ",
    "ye": "ㄧㄝ",
    "yi": "ㄧ",
    "yin": "ㄧㄣ",
    "ying": "ㄧㄥ",
    "yo": "ㄧㄛ",
    "yong": "ㄩㄥ",
    "you": "ㄧㄡ",
    "yu": "ㄩ",
    "yuan": "ㄩㄢ",
    "yue": "ㄩㄝ",
    "yun": "ㄩㄣ",
    "za": "ㄗㄚ",
    "zai": "ㄗㄞ",
    "zan": "ㄗㄢ",
    "zang": "ㄗㄤ",
    "zao": "ㄗㄠ",
    "ze": "ㄗㄜ",
    "zei": "ㄗㄟ",
    "zen": "ㄗㄣ",
    "zeng": "ㄗㄥ",
    "zha": "ㄓㄚ",
    "zhai": "ㄓㄞ",
    "zhan": "ㄓㄢ",
    "zhang": "ㄓㄤ",
    "zhao": "ㄓㄠ",
    "zhe": "ㄓㄜ",
    "zhei": "ㄓㄟ",
    "zhen": "ㄓㄣ",
    "zheng": "ㄓㄥ",
    "zhi": "ㄓ",
    "zhong": "ㄓㄨㄥ",
    "zhou": "ㄓㄡ",
    "zhu": "ㄓㄨ",
    "zhua": "ㄓㄨㄚ",
    "zhuai": "ㄓㄨㄞ",
    "zhuan": "ㄓㄨㄢ",
    "zhuang": "ㄓㄨㄤ",
    "zhui": "ㄓㄨㄟ",
    "zhun": "ㄓㄨㄣ",
    "zhuo": "ㄓㄨㄛ",
    "zi": "ㄗ",
    "zong": "ㄗㄨㄥ",
    "zou": "ㄗㄡ",
    "zu": "ㄗㄨ",
    "zuan": "ㄗㄨㄢ",
    "zui": "ㄗㄨㄟ",
    "zun": "ㄗㄨㄣ",
    "zuo": "ㄗㄨㄛ",
    "ȇ": "ㄝ",
}

# z2p = {v: k for k, v in p2z.items()}

# bpmf_tone = {"": "1", "ˊ": "2", "ˇ": "3", "ˋ": "4", "˙": "5"}

# bpmf_tone_inv = {v: k for k, v in bpmf_tone.items()}

# }
# Mapping tone-marked vowels to base + tone
diacritic_tones = {
    'ā': ('a', 'ˉ'), 'á': ('a', 'ˊ'), 'ǎ': ('a', 'ˇ'), 'à': ('a', 'ˋ'),
    'ē': ('e', 'ˉ'), 'é': ('e', 'ˊ'), 'ě': ('e', 'ˇ'), 'è': ('e', 'ˋ'),
    'ī': ('i', 'ˉ'), 'í': ('i', 'ˊ'), 'ǐ': ('i', 'ˇ'), 'ì': ('i', 'ˋ'),
    'ō': ('o', 'ˉ'), 'ó': ('o', 'ˊ'), 'ǒ': ('o', 'ˇ'), 'ò': ('o', 'ˋ'),
    'ū': ('u', 'ˉ'), 'ú': ('u', 'ˊ'), 'ǔ': ('u', 'ˇ'), 'ù': ('u', 'ˋ'),
    'ǖ': ('ü', 'ˉ'), 'ǘ': ('ü', 'ˊ'), 'ǚ': ('ü', 'ˇ'), 'ǜ': ('ü', 'ˋ'),
}

bopomofo_order = {
    # Initial consonants
    'ㄅ': '01', 'ㄆ': '02', 'ㄇ': '03', 'ㄈ': '04',
    'ㄉ': '05', 'ㄊ': '06', 'ㄋ': '07', 'ㄌ': '08',
    'ㄍ': '09', 'ㄎ': '10', 'ㄏ': '11',
    'ㄐ': '12', 'ㄑ': '13', 'ㄒ': '14',
    'ㄓ': '15', 'ㄔ': '16', 'ㄕ': '17', 'ㄖ': '18',
    'ㄗ': '19', 'ㄘ': '20', 'ㄙ': '21',

    # Medials / finals
    'ㄧ': '22', 'ㄨ': '23', 'ㄩ': '24',
    'ㄚ': '25', 'ㄛ': '26', 'ㄜ': '27', 'ㄝ': '28',
    'ㄞ': '29', 'ㄟ': '30', 'ㄠ': '31', 'ㄡ': '32',
    'ㄢ': '33', 'ㄣ': '34', 'ㄤ': '35', 'ㄥ': '36',
    'ㄦ': '37',

    # Tone marks (optional)
    '˙': '38',  # light tone
    'ˉ': '39',  # first tone
    'ˊ': '40',  # second tone
    'ˇ': '41',  # third tone
    'ˋ': '42',  # fourth tone
}

def convert_diacritical_pinyin_to_bopomofo(pinyin_text):
    def normalize(syllable):
        tone = ''
        result = ''
        for char in syllable:
            if char in diacritic_tones:
                base_char, tone = diacritic_tones[char]
                result += base_char
            else:
                result += char
        return result, tone

    bopomofo_output = []
    for syllable in pinyin_text.lower().split():
        base, tone = normalize(syllable)
        bopo = pinyin_to_bopomofo.get(base, base)  # fallback to base if not found
        bopomofo_output.append(bopo + tone)
    return ' '.join(bopomofo_output)

# Examples
#convert_diacritical_pinyin_to_bopomofo("zhōng wén")

#This output shows tone marks (ˉ, ˊ, ˇ, ˋ) appended to Bopomofo syllables.
#You can now integrate this function into your database update script using the termezh_pinyin field.

# Output: 'ㄓㄨㄥˉ ㄨㄣˊ'
def get_bopomofo_from_pinyin(pinyin):
    return convert_diacritical_pinyin_to_bopomofo(pinyin)

# Load stroke count data
stroke_data = {}
with open(unihan_irgsources, 'r', encoding='utf-8') as file:
    for line in file:
        match = re.match(r'^U\+([0-9A-F]+)\s+kTotalStrokes\s+(\d+)$', line.strip())
        if match:
            stroke_data[chr(int(match[1], 16))] = int(match[2])

# Load pinyin data
pinyin_data = {}
tone_map = {'1': {'a': 'ā', 'e': 'ē', 'i': 'ī', 'o': 'ō', 'u': 'ū'},
            '2': {'a': 'á', 'e': 'é', 'i': 'í', 'o': 'ó', 'u': 'ú'},
            '3': {'a': 'ǎ', 'e': 'ě', 'i': 'ǐ', 'o': 'ǒ', 'u': 'ǔ'},
            '4': {'a': 'à', 'e': 'è', 'i': 'ì', 'o': 'ò', 'u': 'ù'}}

def remove_diacritics(text):
    diacritic_map = {
        'ā': 'a', 'á': 'a', 'ǎ': 'a', 'à': 'a',
        'ē': 'e', 'é': 'e', 'ě': 'e', 'è': 'e',
        'ī': 'i', 'í': 'i', 'ǐ': 'i', 'ì': 'i',
        'ō': 'o', 'ó': 'o', 'ǒ': 'o', 'ò': 'o',
        'ū': 'u', 'ú': 'u', 'ǔ': 'u', 'ù': 'u',
        'ǖ': 'u', 'ǘ': 'u', 'ǚ': 'u', 'ǜ': 'u',  # Convert ü variants to u
        'ü': 'u'  # Handle plain ü as well
    }
    return ''.join(diacritic_map.get(c, c) for c in text)


# Read Unihan file and extract tone pinyin
with open(unihan_readings, 'r', encoding='utf-8') as file:
    for line in file:
        match = re.match(r'^U\+([0-9A-F]+)\s+kMandarin\s+(.+)$', line.strip()) # "U+3400	kMandarin	qiū"
        if match:
            char = chr(int(match[1], 16))
            pinyin = match[2].split(' ')[0]  # Use the first Pinyin reading but in fact the diacritic_pinyin 	"qiū"

            nondiacr_pinyin = remove_diacritics(pinyin)
#           nondiacr_pinyin = unidecode.unidecode(pinyin)
            
            pinyin_data[char] = {'termezh_pinyin': nondiacr_pinyin, 'termezh_diacrpinyin': pinyin}
print(f"Loaded Strokes: {len(stroke_data)} entries")
print(f"Loaded Pinyin: {len(pinyin_data)} entries")

#####
cursor.execute(f"SELECT edition, section, numterme, entree, terme, termeen, maj, intexte, nouveau FROM spip_demoindex WHERE edition = 'zh-ii'")

print ("hello")
rows = cursor.fetchall()

#for terme, in cursor.fetchall():
# Testing
count = 0

for row in rows:
    edition = row['edition']
    section = row['section']
    numterme = row['numterme']
    entree = row['entree']
    maj = row['maj']
    intexte = row['intexte']
    nouveau = row['nouveau']
    #    terme = row[0]
    terme = row['terme']
    termezh = terme # default is simplified
    if args.traditional:
        # New separating traditional from simplified
        # Convert to Traditional
        termezh = cc_s2t.convert(terme)
    
        # Now work with the traditional characters
        characters = list(termezh)
    else:
        characters = list(terme)
    #    termeen = row[1]
    termeen = row['termeen']
    #englishcharacters = list(termeen)

    if args.traditional:
        print(f"Processing: {terme} TRADITIONAL")  # Debug output
    else:
        print(f"Processing: {terme} default ie SIMPLIFIED")  # Debug output

    stroke_counts = [stroke_data.get(c, 0) for c in characters]
    pinyin_list = [pinyin_data.get(c, {}).get("termezh_pinyin", "") for c in characters]
    diacr_pinyin_list = [pinyin_data.get(c, {}).get("termezh_diacrpinyin", "") for c in characters]
    # hex_strokes = ''.join([format(min(s, 15), 'x') for s in stroke_counts])  # Convert to hex string
    hex_strokes = ''.join([format(s, '02x') for s in stroke_counts]) # pads with zero: 1 => '01', 22 => '16'
    
    # Get separator based on the stroke count of the first character
    first_char_strokes = stroke_counts[0] if stroke_counts else 0  # Avoid index error for empty terms
    strokes_separator = stroke_labels.get(first_char_strokes, "非笔画字符")  # Default to "十六画"

    bopomofo_list = [convert_diacritical_pinyin_to_bopomofo(syllable) for syllable in diacr_pinyin_list]
    bopomofo_string = ''.join(bopomofo_list)  # Flatten the list into one string without spaces
    bopomofo_sort_key_list = ''.join(bopomofo_order.get(char, '99') for char in bopomofo_string if char.strip())
    
    print(f"Characters: {characters}")  
    print(f"Edition: {edition}, Section: {section}, Terme: {terme}, Termeen: {termeen}")
    print(f"termezh: {termezh}")  
    print(f"termeen: {termeen}")  
    print(f"Strokes: {stroke_counts}")  
    print(f"Hexstrokes: {hex_strokes}")  
    print(f"Strokes-separator: {strokes_separator}")  
    print(f"Pinyin: {pinyin_list}")  
    print(f"Diacritic Pinyin: {diacr_pinyin_list}")
    print(f"Bopomofo: {bopomofo_list}")  
    print(f"Bopomofo_sort_key: {bopomofo_sort_key_list}")
    # print(f"First Bopomofo: {firstcharbopomofo}") # Will be output later like for firstchar_pinyin 
    print(f"Count: ",count)
    count = count + 1
    if count >=10 : break
# # Define your extra columns as (name, type) pairs
# columns = [
#     ("termezh_pinyin", "VARCHAR(255)"),
#     ("termezh_diacrpinyin", "VARCHAR(255)"),
#     ("termezh_firstchar_pinyin", "CHAR(1)"),
#     ("termezh_strokes", "INT(11)"),
#     ("termezh_HEX_strokes", "VARCHAR(255)"),
#     ("first_english", "CHAR(1)"),
#     ("termezh_strokes_separator", "VARCHAR(10)"),
#     ("termezh_bopomofo", "VARCHAR(255)"),
#     ("termezh_firstchar_bopomofo", "VARCHAR(10)"),
#     ("termezh_bopomofo_sort_key", "VARCHAR(255)"),
# ]


print(f"End first loop count=: ",count)

for row in rows:
    edition = row['edition']
    section = row['section']
    numterme = row['numterme']
    entree = row['entree']
    maj = row['maj']
    intexte = row['intexte']
    nouveau = row['nouveau']
    #    terme = row[0]
    terme = row['terme']
    #    termeen = row[1]
    termeen = row['termeen']
    termezh = terme # Default is as in the wiki ie simplified
    
    if args.traditional:
        # New separating traditional from simplified
    
        # Convert to Traditional
        termezh = cc_s2t.convert(terme)
    
        # Now work with the traditional characters
        characters = list(termezh)
    else:
        characters = list(terme)
     
    # Get stroke counts for each character
    stroke_counts = [stroke_data.get(c, 0) for c in characters]
    
    # Total stroke count (not used for the separator anymore)
    strokes = sum(stroke_counts)

    # Get pinyin transcriptions
    pinyin = ' '.join([pinyin_data.get(c, {}).get('termezh_pinyin', '') for c in characters])
    diacr_pinyin = ' '.join([pinyin_data.get(c, {}).get('termezh_diacrpinyin', '') for c in characters])
    # Convert strokes to hex format
    # hex_strokes = ''.join([format(min(s, 15), 'x') for s in stroke_counts])  # Convert to hex string
    hex_strokes = ''.join([format(s, '02x') for s in stroke_counts]) # pads with zero: 1 => '01', 22 => '16'

    
    # Get separator based on the stroke count of the first character
    first_char_strokes = stroke_counts[0] if stroke_counts else 0  # Avoid index error for empty terms
    strokes_separator = stroke_labels.get(first_char_strokes, "非笔画字符")  # Default to "十六画"

    
    # Convert to Bopomofo syllables
    bopomofo = ' '.join(convert_diacritical_pinyin_to_bopomofo(syllable) for syllable in diacr_pinyin.split())

    # Generate sort key based on individual Bopomofo characters
    bopomofo_sort_key = ''.join(bopomofo_order.get(char, '99') for char in bopomofo.replace(' ', '') if char.strip())


    # cursor.execute("INSERT INTO spip_demoindexzh (terme, termeen, termezh_strokes, termezh_pinyin, termezh_diacr_pinyin, termezh_HEX_strokes, termezh_strokes_separator) VALUES (%s, %s, %s, %s, %s, %s, %s)",
    #                (terme, termeen, strokes, pinyin, diacr_pinyin, hex_strokes, strokes_separator))
    # cursor.execute("INSERT INTO spip_demoindexzh (terme, termeen, termezh_pinyin, termezh_diacrpinyin, termezh_HEX_strokes, termezh_strokes_separator, termezh_strokes) VALUES (%s, %s, %s, %s, %s, %s, %s)",
    #                (terme, termeen, pinyin, diacr_pinyin, hex_strokes, strokes_separator, strokes))
    #cursor.execute(f"INSERT INTO `{tabledemo}` (edition, section, numterme, entree, terme, termeen, maj, intexte, nouveau, termezh_strokes, termezh_pinyin, termezh_diacrpinyin, termezh_HEX_strokes, termezh_strokes_separator) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", (edition, section, numterme, entree, terme, termeen, maj, intexte, nouveau, strokes, pinyin, diacr_pinyin, hex_strokes, strokes_separator))
    cursor.execute(f"INSERT INTO `{tabledemo}` (edition, section, numterme, entree, terme, termezh, termeen, maj, intexte, nouveau, termezh_strokes, termezh_pinyin, termezh_diacrpinyin, termezh_HEX_strokes, termezh_strokes_separator, termezh_bopomofo, termezh_bopomofo_sort_key) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",(edition, section, numterme, entree, terme, termezh, termeen, maj, intexte, nouveau, strokes, pinyin, diacr_pinyin, hex_strokes, strokes_separator, bopomofo, bopomofo_sort_key))

# end loop

cursor.execute(f"UPDATE `{tabledemo}` SET termezh_firstchar_pinyin = UPPER(LEFT(termezh_pinyin, 1)) WHERE termezh_pinyin IS NOT NULL AND termezh_pinyin <> '';")

cursor.execute(f"UPDATE `{tabledemo}` SET termezh_firstchar_bopomofo = LEFT(termezh_bopomofo, 1) WHERE termezh_bopomofo IS NOT NULL AND termezh_bopomofo <> '';")

#cursor.execute("UPDATE `{tabledemo}` SET first_pinyin_strokes = LEFT(termezh_HEX_strokes, 1) WHERE termezh_HEX_strokes IS NOT NULL AND termezh_HEX_strokes <> '';")

# ASCII did not always work. cursor.execute("UPDATE spip_demoindex_zh SET first_english = UPPER(LEFT(CONVERT(termeen USING ASCII), 1)) WHERE termeen IS NOT NULL AND termeen <> '';")
cursor.execute(f"UPDATE `{tabledemo}` SET first_english = UPPER(LEFT(termeen, 1)) WHERE termeen IS NOT NULL AND termeen <> '';")
# for col_name, col_type in columns:
#     add_column_if_not_exists(cursor, tabledemo, col_name, col_type)

#cursor.execute(f"SELECT edition, section, numterme, entree, terme, termeen, maj, intexte, nouveau FROM spip_demoindex WHERE edition = 'zh-ii'")

    
# Commit changes and close connection
conn.commit()
cursor = conn.cursor()
cursor.execute(f"SHOW CREATE TABLE `{tabledemo}`")
print("End Show")
result = cursor.fetchone()
print("End")
if result:
    print(result)  # or print(result[1]) to inspect the whole row
    print("End after result")
else:
    print("No result returned.")
cursor.close()
conn.close()



#SHOW TABLES LIKE 'spip_demoindex_trad';
#DESCRIBE spip_demoindex_trad;

print("Updated database with tone-marked pinyin!")
