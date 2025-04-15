import time
from mysql.connector.cursor import MySQLCursorDict
import pytest
from plugin import plugin_activate, plugin_deactivate, table_count


@pytest.mark.serial
def test_plugin_deactivate(cursor: MySQLCursorDict):
    """test plugin deactivation kills tables"""

    rs = plugin_deactivate()
    assert "success" in rs.lower()

    # table count
    time.sleep(1)
    cnt = table_count(cursor)
    assert cnt == 0


@pytest.mark.serial
def test_plugin_activate(cursor: MySQLCursorDict):
    """test plugin activation"""
    # active
    rs = plugin_activate()
    assert "success" in rs.lower()

    # table count
    time.sleep(1)
    cnt = table_count(cursor)
    assert cnt > 0
    assert cnt == 4
