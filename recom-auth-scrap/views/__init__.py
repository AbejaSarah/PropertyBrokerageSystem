from flask import Blueprint

views_blueprint = Blueprint('views', __name__)

from .home import *
from .auth import *
