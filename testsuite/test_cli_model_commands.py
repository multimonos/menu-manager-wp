import json

import pytest
from plugin import mmcli

CliTokens = list[list[str]]


@pytest.fixture(scope="module")
def model_get_cmds() -> CliTokens:
    return [
        ["backup", "get", "0"],
        ["job", "get", "0"],
        ["node", "get", "0"],
    ]


@pytest.fixture(scope="module")
def model_list_json_cmds() -> CliTokens:
    return [
        ["backup", "ls", "--format=json"],
        ["job", "ls", "--format=json"],
        ["node", "ls", "--format=json"],
    ]


@pytest.fixture(scope="module")
def model_list_count_cmds() -> CliTokens:
    return [
        ["backup", "ls", "--format=count"],
        ["job", "ls", "--format=count"],
        ["node", "ls", "--format=count"],
    ]


@pytest.fixture(scope="module")
def model_list_ids_cmds() -> CliTokens:
    return [
        ["backup", "ls", "--format=ids"],
        ["job", "ls", "--format=ids"],
        ["node", "ls", "--format=ids"],
    ]


@pytest.fixture(scope="module")
def model_list_table_cmds() -> CliTokens:
    return [
        ["backup", "ls", "--format=table"],
        ["job", "ls", "--format=table"],
        ["node", "ls", "--format=table"],
    ]


def test_list_table_commands(model_list_table_cmds: CliTokens):
    """default view is 'table', so, check against cli call without --format"""
    for args in model_list_table_cmds:
        rs = mmcli(*args)
        assert rs.returncode == 0
        assert isinstance(rs.stdout, str)
        assert "+-" in rs.stdout

        default = mmcli(*args[:2])
        assert default.returncode == 0

        assert default.stdout == rs.stdout


def test_list_json_commands(model_list_json_cmds: CliTokens):
    for args in model_list_json_cmds:
        rs = mmcli(*args)
        assert rs.returncode == 0
        v = json.loads(rs.stdout)
        assert isinstance(v, list)


def test_list_count_commands(model_list_count_cmds: CliTokens):
    for args in model_list_count_cmds:
        rs = mmcli(*args)
        assert rs.returncode == 0
        v = int(rs.stdout.strip())
        assert isinstance(v, int)
        assert v >= 0


def test_list_ids_commands(model_list_ids_cmds: CliTokens):
    for args in model_list_ids_cmds:
        rs = mmcli(*args)
        assert rs.returncode == 0

        assert isinstance(rs.stdout, str)

        # empty str is ok when count=0
        if rs.stdout.strip() == "":
            continue

        # ids is list
        ids = rs.stdout.strip().split(" ")
        assert len(ids) >= 0

        # ids are all ints
        if len(ids) > 0:
            try:
                [int(x) for x in ids]
            except ValueError:
                pytest.fail(
                    f"not all terms of the 'ids' string '{rs.stdout}' are valid integers"
                )


def test_get_commands(model_get_cmds: CliTokens):
    for args in model_get_cmds:
        rs = mmcli(*args)
        assert rs.returncode == 0
        o = json.loads(rs.stdout)
        assert "success" in o
        assert "message" in o
        assert "data" in o


def test_job_latest_cmd():
    rs = mmcli("job", "latest")
    assert rs.returncode == 0
    o = json.loads(rs.stdout)
    assert "success" in o
    assert "message" in o
    assert "data" in o
