import subprocess
import time
from mysql.connector import MySQLConnection, cursor
from mysql.connector.cursor import MySQLCursorDict
from wordpress import Config, Database, config_item, get_config

PLUGIN = "menu-manager-wp"


def test_get_config():
    config = get_config()
    assert isinstance(config, list)
    assert len(config) > 0
    assert isinstance(config[0], dict)
    keys = config[0].keys()
    assert ["name", "value", "type"] == list(keys)


def test_config_has_db_keys(wp_config: Config):
    keys = [x["name"] for x in wp_config]

    dbkeys = [
        "DB_NAME",
        "DB_HOST",
        "DB_USER",
        "DB_PASSWORD",
    ]
    for k in dbkeys:
        assert k in keys

    for k in dbkeys:
        v = config_item(wp_config, k)
        assert v is not None


def test_db_config(db_config: Database):
    assert db_config["user"] is not None
    assert db_config["host"] is not None
    assert db_config["password"] is not None
    assert db_config["database"] is not None


def test_db_conn(db_conn: MySQLConnection):
    print(db_conn)


def test_db_cursor_from_conn(db_conn: MySQLConnection):
    cursor = db_conn.cursor()
    cursor.execute("select 1;")
    row = cursor.fetchone()
    assert row is not None
    assert row[0] == 1


def test_db_cursor(db_cursor: MySQLCursorDict):
    db_cursor.execute("select 1 as value;")
    row = db_cursor.fetchone()
    print(f"row:{row}")
    assert row is not None
    assert row["value"] == 1


def test_plugin_deactivate(db_cursor: MySQLCursorDict):
    """test plugin deactivation kills tables"""

    # deactivate
    subprocess.run(["wp", "plugin", "deactivate", PLUGIN], check=False)
    rs = subprocess.run(
        ["wp", "plugin", "status", PLUGIN], check=True, capture_output=True
    )
    assert "inactive" in rs.stdout.decode().lower()

    # table count
    time.sleep(1)
    db_cursor.execute("show tables like 'wp_mm_%';")
    tables = db_cursor.fetchall()
    assert len(tables) == 0


def test_plugin_activate(db_cursor: MySQLCursorDict):
    """test plugin activation"""
    # active
    subprocess.run(["wp", "plugin", "activate", PLUGIN], check=False)
    rs = subprocess.run(
        ["wp", "plugin", "status", PLUGIN], check=True, capture_output=True
    )
    output = rs.stdout.decode().lower()
    assert "inactive" not in output
    assert "active" in output

    # table count
    time.sleep(1)
    db_cursor.execute("show tables like 'wp_mm_%';")
    tables = db_cursor.fetchall()
    print(tables)
    assert len(tables) > 0
