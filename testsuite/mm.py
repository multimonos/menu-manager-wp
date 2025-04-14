# test_menu_manager.py
import subprocess
import time
import pytest
import mysql.connector
from pathlib import Path
import os
import json

def get_wp_config_value(key):
    """Retrieve a value from wp-config.php using WP-CLI."""
    try:
        result = subprocess.run(
    ["wp", "config", "get", key, "--allow-root", "--format=json"],
            capture_output=True, text=True, check=True
        )
        return json.loads(result.stdout.strip())
    except (subprocess.SubprocessError, json.JSONDecodeError) as e:
        print(f"Error getting WP config value for {key}: {e}")
        return None

def get_db_config():
    """Get database configuration from WordPress."""
    return {
        "host": get_wp_config_value("DB_HOST"),
        "user": get_wp_config_value("DB_USER"),
        "password": get_wp_config_value("DB_PASSWORD"),
        "database": get_wp_config_value("DB_NAME")
    }

PLUGIN_NAME = "menu-manager-wp"
TEST_DATA_DIR = Path("./data")
TEST_CSV = TEST_DATA_DIR / "valid_create.csv"

@pytest.fixture(scope="module")
def db_config():
    """Retrieve database configuration from WordPress."""
    config = get_db_config()
    
    # Verify we got all required database config values
    missing = [k for k, v in config.items() if v is None]
    if missing:
        pytest.fail(f"Missing required database config values: {', '.join(missing)}")
    
    return config

@pytest.fixture(scope="module")
def db_conn(db_config):
    """Create a database connection using WP-CLI retrieved config."""
    # Allow for connection retry
    max_retries = 5
    retry_delay = 5  # seconds
    
    for attempt in range(max_retries):
        try:
            print(f"Connecting to database with config: {db_config}")
            connection = mysql.connector.connect(**db_config)
            yield connection
            connection.close()
            return
        except mysql.connector.Error as err:
            if attempt < max_retries - 1:
                print(f"Database connection attempt {attempt+1} failed: {err}. Retrying in {retry_delay}s...")
                time.sleep(retry_delay)
            else:
                raise

@pytest.fixture(scope="module")
def db_cursor(db_conn):
    """Create a cursor from the database connection."""
    cursor = db_conn.cursor(dictionary=True)
    yield cursor
    cursor.close()

@pytest.fixture(scope="module")
def wordpress_ready():
    """Ensure WordPress is ready before tests begin."""
    max_retries = 10
    retry_delay = 3
    
    for attempt in range(max_retries):
        try:
            result = subprocess.run(
                ["wp", "core", "is-installed", "--allow-root"],
                capture_output=True, check=False
            )
            if result.returncode == 0:
                # Get WordPress version to verify everything is working
                version = subprocess.run(
                    ["wp", "core", "version", "--allow-root"],
                    capture_output=True, text=True, check=True
                )
                print(f"WordPress {version.stdout.strip()} is ready!")
                return
        except subprocess.SubprocessError:
            pass
        
        print(f"WordPress not ready, attempt {attempt+1}/{max_retries}. Waiting {retry_delay}s...")
        time.sleep(retry_delay)
    
    pytest.fail("WordPress failed to become ready")

@pytest.fixture(scope="function")
def clean_environment(wordpress_ready, db_conn):
    """Set up a clean environment before each test."""
    # Get WordPress prefix for table names
    prefix_result = subprocess.run(
        ["wp", "db", "prefix", "--allow-root"],
        capture_output=True, text=True, check=True
    )
    prefix = prefix_result.stdout.strip()
    
    # Deactivate and reactivate plugin
    subprocess.run(["wp", "plugin", "deactivate", PLUGIN_NAME, "--allow-root"], check=True)
    time.sleep(1)  # Brief pause for stability
    subprocess.run(["wp", "plugin", "activate", PLUGIN_NAME, "--allow-root"], check=True)
    
    # Clean database state using make target
    subprocess.run(["make", "delete-data"], check=True)
    
    # Return connection for use in test if needed
    yield db_conn
    
    # Clean up any files created during test
    subprocess.run(["make", "clean"], check=True)

def execute_query(db_conn, query, params

   assert table in found_tables, f"Table {table} not found in database"
