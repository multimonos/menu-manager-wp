import csv
from pathlib import Path

from const import A_CSV, A_SLUG, AB_CSV, B_CSV, B_SLUG


def test_csv_a_exists():
    path = Path(A_CSV)
    assert path.exists()


def test_csv_b_exists():
    path = Path(B_CSV)
    assert path.exists()


def test_csv_ab_exists():
    path = Path(AB_CSV)
    assert path.exists()


def test_csv_a_has_valid_slug():
    path = Path(A_CSV)
    with path.open("r") as f:
        reader = csv.DictReader(f)
        menus = [row["menu"] for row in reader]
    unique_menus = sorted(list(set(menus)))
    assert unique_menus == [A_SLUG]


def test_csv_b_has_valid_slug():
    path = Path(B_CSV)
    with path.open("r") as f:
        reader = csv.DictReader(f)
        menus = [row["menu"] for row in reader]
    unique_menus = sorted(list(set(menus)))
    assert unique_menus == [B_SLUG]


def test_csv_ab_has_valid_slug():
    path = Path(AB_CSV)
    with path.open("r") as f:
        reader = csv.DictReader(f)
        menus = [row["menu"] for row in reader]
    unique_menus = sorted(list(set(menus)))
    assert unique_menus == [A_SLUG, B_SLUG]
