from mysql.connector.cursor import MySQLCursorDict
import pytest
import mysql.connector
from mysql.connector.connection import MySQLConnection
from wordpress import Config, Database, get_config, get_dbconfig
from collections.abc import Generator


def pytest_configure(config):
    config.option.maxprocesses = 1


@pytest.fixture(scope="session")
def wp_config() -> Config:
    """get all wordpress config"""
    config = get_config()
    return config


@pytest.fixture(scope="session")
def db_config() -> Database:
    """get wordpress database config"""
    return get_dbconfig()


@pytest.fixture(scope="function")
def db_conn(db_config: Database) -> Generator[MySQLConnection, None, None]:
    """Create a database connection using WP-CLI retrieved config."""
    max_retries = 5
    retry_delay = 5  # seconds

    for attempt in range(max_retries):
        try:
            conn = mysql.connector.connect(**db_config)
            yield conn
            conn.close()
            return
        except mysql.connector.Error as err:
            if attempt < max_retries - 1:
                time.sleep(retry_delay)
            else:
                raise


@pytest.fixture(scope="function")
def db_cursor(db_conn: MySQLConnection) -> Generator[MySQLCursorDict, None, None]:
    """Create a cursor from the database connection."""
    cursor: MySQLCursorDict = db_conn.cursor(dictionary=True)
    yield cursor
    cursor.close()
