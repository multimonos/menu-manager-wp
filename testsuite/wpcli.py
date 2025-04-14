import subprocess

from mysql.connector.cursor import MySQLCursorDict


def plugin_activate(name: str) -> str:
    rs = subprocess.run(
        ["wp", "plugin", "activate", name], check=False, capture_output=True
    )
    return rs.stdout.decode()


def plugin_deactivate(name: str) -> str:
    rs = subprocess.run(
        ["wp", "plugin", "deactivate", name], check=False, capture_output=True
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
