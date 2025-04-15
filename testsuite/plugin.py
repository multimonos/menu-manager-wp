from pathlib import Path
from subprocess import run, CompletedProcess
import json
from typing import Any, cast
from model import Job, Menu
from mysql.connector.cursor import MySQLCursorDict

from const import MENU_TYPE, PLUGIN_NAME


def csv_exists(filepath: str) -> bool:
    path = Path(filepath)
    return path.exists()


def sql_count(cursor: MySQLCursorDict, sql: str) -> int:
    sql_print(sql)
    cursor.execute(sql)
    row: dict[str, Any] | None = cursor.fetchone()
    if row is None:
        return -1
    val = row.get("cnt")
    return int(val) if val is not None else -1


def sql_print(sql: str) -> None:
    print(f"SQL  {sql}")


def wpcli(*args: str) -> CompletedProcess[str]:
    rs = run(["wp", *args], check=False, capture_output=True, text=True)
    print(f"CLI  args={rs.args}, returncode={rs.returncode}, stderr={rs.stderr}")
    return rs


def mmcli(*args: str) -> CompletedProcess[str]:
    return wpcli("mm", *args)


def task_success(rs: CompletedProcess[str]) -> bool:
    return rs.returncode == 0 and "success" in rs.stdout.lower()


def plugin_clean(cursor: MySQLCursorDict) -> None:
    sql = f"delete from wp_posts where post_type = '{MENU_TYPE}';"
    sql_print(sql)
    cursor.execute(sql)


def plugin_activate() -> str:
    rs = wpcli("plugin", "activate", PLUGIN_NAME)
    return rs.stdout


def plugin_deactivate() -> str:
    rs = wpcli("plugin", "deactivate", PLUGIN_NAME)
    return rs.stdout


def table_count(cursor: MySQLCursorDict) -> int:
    sql = "show tables like 'wp_mm_%';"
    print(sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    return len(rows)


def menu_count(cursor: MySQLCursorDict) -> int:
    sql = f"select count(*) as cnt from wp_posts where post_type = '{MENU_TYPE}';"
    return sql_count(cursor, sql)


def menu_exists(cursor: MySQLCursorDict, name: str) -> bool:
    sql = f"select count(*) as cnt from wp_posts where post_type = '{MENU_TYPE}' and post_name='{name}';"
    return sql_count(cursor, sql) == 1


def menu_get(name: str) -> Menu | None:
    rs = mmcli("menu", "get", name)

    if rs.returncode != 0:
        return None

    try:
        raw: dict[str, str] = json.loads(rs.stdout.strip())
        print(raw)

        if isinstance(raw, dict):
            o = cast(Menu, raw)
            print(o)
            return o

        return None
    except:
        return None


def node_count(cursor: MySQLCursorDict) -> int:
    return sql_count(cursor, "select count(*) as cnt from wp_mm_node;")


def impex_count(cursor: MySQLCursorDict) -> int:
    return sql_count(cursor, "select count(*) as cnt from wp_mm_impex;")


def impex_load(filepath: str) -> CompletedProcess[str]:
    return mmcli("import", "load", filepath)


def job_exists(cursor: MySQLCursorDict, id: int) -> bool:
    sql = f"select count(*) as cnt from wp_mm_jobs where id='{id}';"
    return sql_count(cursor, sql) == 1


def job_get(id: int) -> Job | None:
    rs = mmcli("job", "get", str(id))

    if rs.returncode != 0:
        return None

    try:
        o: dict[str, str] = json.loads(rs.stdout.strip())

        raw = o.get("Job")

        if isinstance(raw, dict):
            print(o)
            o = cast(Job, raw)
            return o

        return None
    except:
        return None


def job_run(id: int) -> CompletedProcess[str]:
    return mmcli("job", "run", str(id))
