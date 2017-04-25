"""
Project:     Crescendia Game
File:        logic_battle_system.py
Descritpion: Holds methods to be called by the controller.py battle resolution.
"""

import random

# placeholder function to resolve battles


def pick_winner_by_chance(user_1_strength, user_2_strength, user_1_id, user_2_id):
    outcome = random.uniform(0, (user_1_strength + user_2_strength))
    if(outcome < user_1_strength):
        return user_1_id
    else:
        return user_2_id
