from typing import TypedDict


class Job(TypedDict):
    id: int
    status: str
    title: str
    filename: str


class Menu(TypedDict):
    ID: int
    post_title: str
    post_name: str
    post_status: str


class NodeMeta(TypedDict):
    id: int
    node_id: int
    tags: str
    prices: str
    image_ids: str


class Node(TypedDict):
    id: int
    menu_id: int
    uuid: str
    parent_id: int
    title: str
    type: str
    description: str
    meta: NodeMeta
