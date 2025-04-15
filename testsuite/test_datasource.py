from pathlib import Path

from const import CSV_ONE, CSV_TWO


def test_csv_one_exists():
    path = Path(CSV_ONE)
    assert path.exists()


def test_csv_two_exists():
    path = Path(CSV_TWO)
    assert path.exists()
