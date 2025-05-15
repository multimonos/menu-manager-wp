from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_COUNT,
    A_CSV,
    A_NODECOUNT,
    PATCH_DELETE_GROUP_CSV,
    PATCH_GROUP_CHILDREN_IDS,
    PATCH_GROUP_ID,
)
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    job_count,
    job_exists,
    job_latest,
    job_run,
    node_count,
    node_exists,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_delete_group(cursor: MySQLCursorDict):
    """delete an option-group node which should cascade delete it's children"""

    ids = [PATCH_GROUP_ID, *PATCH_GROUP_CHILDREN_IDS]

    # setup
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load
    assert csv_exists(A_CSV)
    assert cli_success(impex_load(A_CSV))
    assert impex_count(cursor) == A_COUNT

    # run
    assert job_count(cursor) == 1
    job = job_latest()
    assert job_exists(cursor, job["id"])
    assert cli_success(job_run(job["id"]))

    # check
    for id in ids:
        assert node_exists(cursor, id)

    # check
    assert node_count(cursor) == A_NODECOUNT

    # load update
    assert csv_exists(PATCH_DELETE_GROUP_CSV)
    assert cli_success(impex_load(PATCH_DELETE_GROUP_CSV))

    # run update
    assert job_count(cursor) == 2
    job2 = job_latest()
    assert job_exists(cursor, job2["id"])
    assert cli_success(job_run(job2["id"]))

    # check
    assert node_count(cursor) == A_NODECOUNT - len(ids)
    for id in ids:
        assert node_exists(cursor, id) == False
