from mysql.connector import MySQLConnection
from mysql.connector.cursor import MySQLCursorDict
from wordpress import Config, Database, config_item, get_config


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


def test_db_conn(conn: MySQLConnection):
    print(conn)


def test_db_cursor_from_conn(conn: MySQLConnection):
    cursor = conn.cursor()
    cursor.execute("select 1;")
    row = cursor.fetchone()
    assert row is not None
    assert row[0] == 1


def test_db_cursor(cursor: MySQLCursorDict):
    cursor.execute("select 1 as value;")
    row = cursor.fetchone()
    print(f"row:{row}")
    assert row is not None
    assert row["value"] == 1
