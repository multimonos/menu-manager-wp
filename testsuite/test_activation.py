from mysql.connector.cursor import MySQLCursorDict
import pytest
from plugin import plugin_activate, plugin_deactivate, table_count, cli_success


@pytest.mark.serial
def test_plugin_deactivate(cursor: MySQLCursorDict):
    """test plugin deactivation"""
    assert cli_success(plugin_deactivate())
    assert table_count(cursor) == 0


@pytest.mark.serial
def test_plugin_activate(cursor: MySQLCursorDict):
    """test plugin activation"""
    assert cli_success(plugin_activate())
    assert table_count(cursor) == 4
