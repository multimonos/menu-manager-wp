DATAPATH = "./data"
MENU_TYPE = "menus"
PLUGIN_NAME = "menu-manager-wp"

# csv
A_CSV = f"{DATAPATH}/a.csv"
A_COUNT = 233
A_NODECOUNT = A_COUNT + 4  # items count + 3 pages and root node
A_SLUG = "aaa-menu"

B_CSV = f"{DATAPATH}/b.csv"
B_COUNT = 224
B_NODECOUNT = B_COUNT + 4  # items count + 3 pages and root node
B_SLUG = "bbb-menu"

C_SLUG = "ccc-menu"

AB_CSV = f"{DATAPATH}/ab.csv"
AB_COUNT = 457
AB_NODECOUNT = A_NODECOUNT + B_NODECOUNT

# patch csv
PATCH_ID = 230
PATCH_ITEM_UUID = 26915
PATCH_UPDATE_CSV = f"{DATAPATH}/patch-update-item.csv"

PATCH_PRICE = 66.69
PATCH_PRICE_CSV = f"{DATAPATH}/patch-price-item.csv"

PATCH_DELETE_ITEM_CSV = f"{DATAPATH}/patch-delete-item.csv"
PATCH_GROUP_ID = 219
PATCH_GROUP_CHILDREN_IDS = [220]

PATCH_DELETE_GROUP_CSV = f"{DATAPATH}/patch-delete-group.csv"

# tables
TBL_POSTS = "wp_posts"
TBL_JOBS = "wp_mm_jobs"
TBL_IMPEX = "wp_mm_impex"
TBL_NODES = "wp_mm_node"
TBL_NODEMETA = "wp_mm_node_meta"
