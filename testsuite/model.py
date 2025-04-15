from typing import TypedDict


class Job(TypedDict):
    id: int
    type: str
    status: str
    source: str


class Menu(TypedDict):
    ID: int
    post_title: str
    post_name: str
    post_status: str
