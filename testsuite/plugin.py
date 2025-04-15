import subprocess


from mysql.connector.cursor import MySQLCursorDict

from const import PLUGIN_NAME


def plugin_activate() -> str:
    rs = subprocess.run(
        ["wp", "plugin", "activate", PLUGIN_NAME], check=False, capture_output=True
    )
    return rs.stdout.decode()


def plugin_deactivate() -> str:
    rs = subprocess.run(
        ["wp", "plugin", "deactivate", PLUGIN_NAME], check=False, capture_output=True
    )
    return rs.stdout.decode()


def table_count(cursor: MySQLCursorDict) -> int:
    cursor.execute("show tables like 'wp_mm_%';")
    rows = cursor.fetchall()
    return len(rows)


def menu_count(cursor: MySQLCursorDict) -> int:
    cursor.execute("select * from wp_posts where post_type = 'menu';")
    rows = cursor.fetchall()
    return len(rows)


def menu_exists(cursor: MySQLCursorDict, name: str) -> bool:
    cursor.execute(
        f"select * from wp_posts where post_type = 'menu' and post_name='{name}';"
    )
    rows = cursor.fetchall()
    return len(rows) > 0
