DATAPATH = "./data"
MENU_TYPE = "menus"
PLUGIN_NAME = "menu-manager-wp"

# tables
TBL_POSTS = "wp_posts"
TBL_JOBS = "wp_mm_jobs"
TBL_IMPEX = "wp_mm_impex"
TBL_NODES = "wp_mm_node"
TBL_NODEMETA = "wp_mm_node_meta"

#
# CSV
# - counts are item count not line count
#
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
AB_COUNT = A_COUNT + B_COUNT
AB_NODECOUNT = A_NODECOUNT + B_NODECOUNT
EXPORT_CSV = f"{DATAPATH}/export.csv"

# patch update
PATCH_ID = 230
PATCH_ITEM_UUID = 26915
PATCH_UPDATE_CSV = f"{DATAPATH}/patch-update-item.csv"

# patch price
PATCH_PRICE = 66.69
PATCH_PRICE_CSV = f"{DATAPATH}/patch-price-item.csv"

# patch delete item
PATCH_DELETE_ITEM_CSV = f"{DATAPATH}/patch-delete-item.csv"

# patch delete group
PATCH_GROUP_ID = 219
PATCH_GROUP_CHILDREN_IDS = [220]
PATCH_DELETE_GROUP_CSV = f"{DATAPATH}/patch-delete-group.csv"
