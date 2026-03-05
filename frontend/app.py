from flask import Flask, render_template, request, redirect, session
import requests
import os

app = Flask(__name__)
app.secret_key = os.getenv('FLASK_SECRET', 'una clave secreta')  # cambiar en producción

API_BASE = os.getenv('API_BASE', 'http://localhost:4000/api')

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        data = {
            'nombre': request.form['nombre'],
            'username': request.form['username'],
            'email': request.form['email'],
            'password': request.form['password']
        }
        r = requests.post(f"{API_BASE}/auth/register", json=data)
        if r.status_code == 201:
            return "Registrado. Espera que un administrador te asigne un rol."
        else:
            return f"Error: {r.text}", r.status_code
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        data = {
            'identifier': request.form['identifier'],
            'password': request.form['password']
        }
        r = requests.post(f"{API_BASE}/auth/login", json=data)
        if r.status_code == 200:
            result = r.json()
            session['token'] = result['token']
            session['rol'] = result['user']['rol']
            # also set a cookie so client-side JS can read it
            resp = redirect('/admin' if session['rol'] == 'ADMIN' else
                            '/vendedor' if session['rol'] == 'VENDEDOR' else
                            '/consultor')
            resp.set_cookie('token', result['token'])
            return resp
        return "Error de login", r.status_code
    return render_template('login.html')

@app.route('/admin')
def admin():
    if session.get('rol') != 'ADMIN':
        return redirect('/login')
    return render_template('admin.html')

@app.route('/vendedor')
def vendedor():
    if session.get('rol') != 'VENDEDOR':
        return redirect('/login')
    return render_template('vendedor.html')

@app.route('/consultor')
def consultor():
    if session.get('rol') != 'CONSULTOR':
        return redirect('/login')
    return render_template('consultor.html')

if __name__ == '__main__':
    app.run(debug=True)
