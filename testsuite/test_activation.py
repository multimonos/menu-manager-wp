from time import sleep
from mysql.connector.cursor import MySQLCursorDict
import pytest
from plugin import (
    impex_count,
    job_count,
    menu_count,
    node_count,
    nodemeta_count,
    plugin_activate,
    plugin_deactivate,
    table_count,
    cli_success,
)


@pytest.mark.serial
def test_plugin_deactivate(cursor: MySQLCursorDict):
    """test plugin deactivation"""
    assert cli_success(plugin_deactivate())
    assert job_count(cursor) == 0
    assert menu_count(cursor) == 0
    assert table_count(cursor) == 0


@pytest.mark.serial
def test_plugin_activate(cursor: MySQLCursorDict):
    """test plugin activation"""
    assert cli_success(plugin_activate())
    assert job_count(cursor) == 0
    assert menu_count(cursor) == 0
    assert impex_count(cursor) == 0
    assert node_count(cursor) == 0
    assert nodemeta_count(cursor) == 0
