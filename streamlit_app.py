"""Streamlit frontend: collect user inputs, query a database, show a clean data table.

Run with:  streamlit run streamlit_app.py
Uses a local SQLite database (inventory.db) that is created and seeded on first run.
"""

from contextlib import closing
from pathlib import Path
import sqlite3

import pandas as pd
import streamlit as st

DB_PATH = Path(__file__).resolve().parent / "inventory.db"

SEED_PRODUCTS = [
    ("Widget A", "Hardware", 150.00, 42),
    ("Widget B", "Hardware", 250.00, 17),
    ("Gadget X", "Electronics", 89.99, 120),
    ("Gadget Y", "Electronics", 199.50, 8),
    ("Gizmo Z", "Accessories", 12.75, 300),
    ("Gizmo Q", "Accessories", 34.20, 65),
    ("Tool T", "Hardware", 45.00, 0),
    ("Sensor S", "Electronics", 320.00, 3),
]


def get_connection() -> sqlite3.Connection:
    """Open a connection with row access enabled."""
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn


def init_db() -> None:
    """Create the products table and seed it once."""
    with closing(get_connection()) as conn:
        with conn:
            conn.execute(
                """
                CREATE TABLE IF NOT EXISTS products (
                    id       INTEGER PRIMARY KEY AUTOINCREMENT,
                    name     TEXT    NOT NULL,
                    category TEXT    NOT NULL,
                    price    REAL    NOT NULL,
                    stock    INTEGER NOT NULL DEFAULT 0
                )
                """
            )
            count = conn.execute("SELECT COUNT(*) FROM products").fetchone()[0]
            if count == 0:
                conn.executemany(
                    "INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)",
                    SEED_PRODUCTS,
                )


def fetch_products(search: str, category: str, min_price: float, max_price: float) -> pd.DataFrame:
    """Run a parameterized query against the database using the user's filters."""
    query = """
        SELECT name, category, price, stock
        FROM products
        WHERE price BETWEEN ? AND ?
          AND (? = 'All' OR category = ?)
          AND (? = '' OR name LIKE ?)
        ORDER BY category, name
    """
    params = (min_price, max_price, category, category, search, f"%{search}%")
    with closing(get_connection()) as conn:
        return pd.read_sql_query(query, conn, params=params)


def main() -> None:
    st.set_page_config(page_title="Inventory Explorer", page_icon=":package:", layout="wide")
    st.title("Inventory Explorer")
    st.caption("Filter the product catalog and inspect the results below.")

    init_db()

    with st.sidebar:
        st.header("Filters")
        search = st.text_input("Search by name", placeholder="e.g. widget")
        category = st.selectbox("Category", ["All", "Hardware", "Electronics", "Accessories"])
        min_price, max_price = st.slider("Price range ($)", 0.0, 500.0, (0.0, 500.0), step=5.0)

    df = fetch_products(search, category, min_price, max_price)

    st.subheader(f"Results ({len(df)})")
    if df.empty:
        st.info("No products match the current filters.")
        return

    st.dataframe(
        df,
        use_container_width=True,
        hide_index=True,
        column_config={
            "name": st.column_config.TextColumn("Product"),
            "category": st.column_config.TextColumn("Category"),
            "price": st.column_config.NumberColumn("Price ($)", format="$%.2f"),
            "stock": st.column_config.NumberColumn("In Stock", format="%d"),
        },
    )

    st.download_button(
        "Download results (CSV)",
        df.to_csv(index=False).encode("utf-8"),
        file_name="inventory_results.csv",
        mime="text/csv",
    )


if __name__ == "__main__":
    main()