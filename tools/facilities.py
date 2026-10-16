"""Facility map markup for the lower homepage."""
from pathlib import Path

def facility_section():
    return (Path(__file__).parent / "pages/facilities.html").read_text().strip()
