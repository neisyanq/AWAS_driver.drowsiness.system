import pygame
import threading

pygame.mixer.init()

def play_alarm():

    pygame.mixer.music.load("sounds/alarm.mp3")
    pygame.mixer.music.play()

def play_warning():

    pygame.mixer.music.load("sounds/audio_warning.mp3")
    pygame.mixer.music.play()

def start_alarm():

    threading.Thread(target=play_alarm).start()

def start_warning():

    threading.Thread(target=play_warning).start()