from flask import Blueprint

from .auth import auth_bp
from .user import user_bp

# Create a blueprint for the controllers
controllers_bp = Blueprint('controllers', __name__)

# Register the blueprints for authentication and user endpoints
controllers_bp.register_blueprint(auth_bp)
controllers_bp.register_blueprint(user_bp)
