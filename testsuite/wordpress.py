from decimal import getcontext
import subprocess
import json
from typing import TypedDict, Any


class ConfigItem(TypedDict):
    name: str
    value: str
    type: str


Config = list[ConfigItem]


class Database(TypedDict):
    user: str
    password: str
    host: str
    database: str


def get_config() -> Config:
    """get a the wp config as json str"""
    try:
        rs = subprocess.run(
            ["wp", "config", "get", "--format=json"], capture_output=True, text=True
        )

        if rs.stdout:
            o: Any = json.loads(rs.stdout.strip())

            if isinstance(o, list) and len(o) > 0 and isinstance(o[0], dict):
                config: Config = o
                return config

        return []

    except (subprocess.SubprocessError, json.JSONDecodeError) as e:
        print(f"failed to get wp config key : {e}")
        return []


def config_item(config: Config, k: str) -> ConfigItem:
    found = list(filter(lambda x: x["name"] == k, config))
    if len(found) != 1:
        raise KeyError(f"key {k} not found inf config")
    return found[0]


def config_value(config: Config, k: str) -> str:
    found = list(filter(lambda x: x["name"] == k, config))
    if len(found) != 1:
        raise KeyError(f"key {k} not found inf config")
    return found[0]["value"]


def get_dbconfig() -> Database:
    config = get_config()
    db: Database = {
        "database": config_value(config, "DB_NAME") or "",
        "host": config_value(config, "DB_HOST") or "",
        "user": config_value(config, "DB_USER") or "",
        "password": config_value(config, "DB_PASSWORD") or "",
    }
    return db


def query(db_conn, query: str, params=None):
    """Helper function to execute a query and return results."""
    cursor = db_conn.cursor(dictionary=True)
    cursor.execute(query, params or ())
    results = cursor.fetchall()
    cursor.close()
    return results
