from pathlib import Path
from subprocess import run, CompletedProcess
import json
import time
from typing import Any, cast
from model import Job, Menu, Node
from mysql.connector.cursor import MySQLCursorDict

from const import (
    MENU_TYPE,
    PLUGIN_NAME,
    TBL_IMPEX,
    TBL_JOBS,
    TBL_NODEMETA,
    TBL_NODES,
    TBL_POSTS,
)


def csv_exists(filepath: str) -> bool:
    path = Path(filepath)
    return path.exists()


def csv_linecount(filepath: str) -> int:
    cnt = -1
    path = Path(filepath)
    if path.exists():
        with open(path, "r") as f:
            doc = f.read()
            cnt = len(doc.splitlines())
            return cnt
    return cnt


def sql_count(cursor: MySQLCursorDict, sql: str) -> int:
    sql_print(sql)
    cursor.execute(sql)
    row: dict[str, Any] | None = cursor.fetchone()
    # print(row)
    if row is None:
        return -1
    val = row.get("cnt")
    return int(val) if val is not None else -1


def sql_print(sql: str) -> None:
    print(f"SQL  {sql}")


"""cli"""


def wpcli(*args: str) -> CompletedProcess[str]:
    rs = run(["wp", *args], check=False, capture_output=True, text=True)
    print(
        f"CLI  call='{' '.join(rs.args)}', returncode={rs.returncode}, stderr={rs.stderr}"
    )
    return rs


def mmcli(*args: str) -> CompletedProcess[str]:
    return wpcli("mm", *args)


def cli_success(rs: CompletedProcess[str]) -> bool:
    return rs.returncode == 0 and "success" in rs.stdout.lower()


"""plugin fns"""


def plugin_reboot(cursor) -> None:
    plugin_deactivate()
    plugin_activate()
    plugin_clean(cursor)


def plugin_clean(cursor: MySQLCursorDict) -> None:
    stmts = [
        f"set foreign_key_checks=0;",
        f"delete from {TBL_POSTS} where post_type = '{MENU_TYPE}';",
        f"truncate {TBL_NODEMETA};",
        f"truncate {TBL_JOBS};",
        f"truncate {TBL_IMPEX};",
        f"truncate {TBL_NODES};",
        f"set foreign_key_checks=1;",
    ]
    for sql in stmts:
        sql_print(sql)
        cursor.execute(sql)

    time.sleep(1)


def plugin_activate() -> CompletedProcess[str]:
    return wpcli("plugin", "activate", PLUGIN_NAME)


def plugin_deactivate() -> CompletedProcess[str]:
    return wpcli("plugin", "deactivate", PLUGIN_NAME)


"""table fns"""


def table_count(cursor: MySQLCursorDict) -> int:
    sql = "show tables like 'wp_mm_%';"
    print(sql)
    cursor.execute(sql)
    rows = cursor.fetchall()
    return len(rows)


def tables_are_empty(cursor) -> bool:
    return (
        menu_count(cursor) == 0
        and job_count(cursor) == 0
        and impex_count(cursor) == 0
        and node_count(cursor) == 0
        and nodemeta_count(cursor) == 0
    )


"""menu fns"""


def menu_count(cursor: MySQLCursorDict) -> int:
    sql = f"select count(*) as cnt from {TBL_POSTS} where post_type = '{MENU_TYPE}';"
    return sql_count(cursor, sql)


def menu_exists(cursor: MySQLCursorDict, name: str) -> bool:
    sql = f"select count(*) as cnt from {TBL_POSTS} where post_type = '{MENU_TYPE}' and post_name='{name}';"
    return sql_count(cursor, sql) == 1


def menu_clone(menu_id_or_slug: str, target: str) -> CompletedProcess[str]:
    return mmcli("menu", "clone", menu_id_or_slug, target)


def menu_get(name: str) -> Menu | None:
    rs = mmcli("menu", "get", name)

    if rs.returncode != 0:
        return None

    try:
        raw: dict[str, str] = json.loads(rs.stdout.strip())
        o = cast(Menu, raw)
        return o
    except:
        return None


"""node fns"""


def is_node(x: Node | None) -> bool:
    return (
        x is not None
        and isinstance(x, dict)
        and isinstance(x.get("id"), int)
        and isinstance(x.get("menu_id"), int)
        and isinstance(x.get("uuid"), str)
        and isinstance(x.get("meta"), dict)
    )


def node_count(cursor: MySQLCursorDict) -> int:
    return sql_count(cursor, f"select count(*) as cnt from {TBL_NODES};")


def node_exists(cursor: MySQLCursorDict, id: int) -> bool:
    sql = f"select count(*) as cnt from {TBL_NODES} where id = {id};"
    return sql_count(cursor, sql) == 1


def node_get(id: int) -> Node | None:
    rs = mmcli("node", "get", str(id))

    if rs.returncode != 0:
        return None

    try:
        raw: dict[str, str] = json.loads(rs.stdout.strip())
        o = cast(Node, raw)
        return o
    except:
        return None


"""node meta fns"""


def nodemeta_count(cursor: MySQLCursorDict) -> int:
    return sql_count(cursor, f"select count(*) as cnt from {TBL_NODEMETA};")


"""impex fns"""


def impex_count(cursor: MySQLCursorDict) -> int:
    return sql_count(cursor, f"select count(*) as cnt from {TBL_IMPEX};")


def impex_load(filepath: str) -> CompletedProcess[str]:
    return mmcli("import", "load", filepath)


def impex_export(menu: str, target: str) -> CompletedProcess[str]:
    return mmcli("export", menu, target)


"""job fns"""


def job_count(cursor: MySQLCursorDict) -> int:
    return sql_count(cursor, f"select count(*) as cnt from {TBL_JOBS};")


def job_exists(cursor: MySQLCursorDict, id: int) -> bool:
    sql = f"select count(*) as cnt from {TBL_JOBS} where id='{id}';"
    return sql_count(cursor, sql) == 1


def job_get(id: int) -> Job | None:
    rs = mmcli("job", "get", str(id))

    if rs.returncode != 0:
        return None

    try:
        raw: dict[str, str] = json.loads(rs.stdout.strip())
        o = cast(Job, raw)
        return o

    except:
        return None


def job_run(id: int) -> CompletedProcess[str]:
    return mmcli("job", "run", str(id))
