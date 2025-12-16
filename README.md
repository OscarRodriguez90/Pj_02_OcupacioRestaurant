# Pokéfull Stack - Sistema de Gestió de Restaurant

**Autors:** GalvezManuel, RodriguezOscar, FernandezHugo

Aplicació web per a la gestió d'ocupació de taules en un restaurant temàtic de Pokémon.

## 📋 Descripció

Sistema de gestió que permet controlar l'ocupació de taules distribuïdes en diferents sales, gestionar reserves per franges horàries, i administrar usuaris amb diferents rols i permisos.

## 🚀 Instal·lació

### Requisits previs
- **WAMP/XAMPP/LAMP** (PHP 7.4+ i MySQL 5.7+)
- Navegador web modern (Chrome, Firefox, Edge)

### Passos d'instal·lació

1. **Clonar o copiar** el projecte a la carpeta del servidor:
   ```
   c:\wamp64\www\DAW2\Projectes\Pj_02_OcupacioRestaurant
   ```

2. **Crear la base de dades**:
   - Obrir phpMyAdmin o un client MySQL
   - Executar el fitxer `database/database.sql`
   - Això crearà la base de dades `bd_pokefullStack` amb totes les taules i dades inicials

3. **Configurar la connexió** (si és necessari):
   - Editar `database/conexion.php` amb les credencials del vostre servidor MySQL

4. **Accedir a l'aplicació**:
   ```
   http://localhost/DAW2/Projectes/Pj_02_OcupacioRestaurant/pages/login.php
   ```

## 👥 Usuaris de Prova

### Usuari Admin (per defecte)
- **Usuari:** `admin`
- **Contrasenya:** `qweQWE123`
- **Rol:** Administrador
- **Permisos:** Accés complet a totes les funcionalitats

### Rols disponibles
El sistema suporta els següents rols:
- **admin**: Accés total, gestió d'usuaris, sales i meses
- **gerent**: Gestió de sales i reserves
- **camarero**: Gestió de reserves i ocupació de taules
- **manteniment**: Gestió de sales i meses
- **caixa**: Consulta d'històric i reserves

## 🎯 Funcionalitats Principals

### 1. Gestió d'Usuaris (Admin)
- Crear, editar i eliminar usuaris
- Assignar rols i permisos
- Activar/desactivar comptes
- Ruta: `pages/admin_usuarios.php`

### 2. Gestió de Sales (Admin/Manteniment)
- Crear i eliminar sales
- Assignar imatges temàtiques
- 9 sales predefinides (regions de Pokémon: Kanto, Johto, Hoenn, Sinnoh, Unova, Kalos, Alola, Galar, Paldea)
- Ruta: `pages/admin_salas.php`

### 3. Gestió de Meses (Admin/Manteniment)
- Afegir i eliminar meses per sala
- Especificar número de cadires
- Estat automàtic (lliure/ocupada)
- Ruta: `pages/admin_mesas.php`

### 4. Sistema de Reserves
- **Filtres disponibles:**
  - Data
  - Franja horària (8:00-23:59 en intervals de 2h)
  - Número mínim de cadires
- **Accions:**
  - Crear reserves per franges horàries
  - Cancel·lar reserves pròpies
  - Visualitzar disponibilitat en temps real
- Ruta: `pages/reservas.php`

### 5. Ocupació de Sales
- Vista interactiva de les taules
- Marcar taules com ocupades/desocupades
- Visualització en temps real de l'estat
- Ruta: `pages/salas/sala.php`

### 6. Històric
- Consulta d'històric general
- Consulta d'històric per sala
- Estadístiques d'ocupació
- Ruta: `pages/historial_general.php`, `pages/historialSala.php`

### 7. Dashboard Administratiu
- Resum de taules ocupades
- Estadístiques del dia
- Accés ràpid a funcions d'administració
- Ruta: `pages/admin_dashboard.php`

## 🔒 Sistema de Permisos

| Funcionalitat | Admin | Gerent | Camarero | Manteniment | Caixa |
|--------------|-------|--------|----------|-------------|-------|
| Gestió d'usuaris | ✅ | ❌ | ❌ | ❌ | ❌ |
| Gestió de sales | ✅ | ✅ | ❌ | ✅ | ❌ |
| Gestió de meses | ✅ | ✅ | ❌ | ✅ | ❌ |
| Crear reserves | ✅ | ✅ | ✅ | ✅ | ✅ |
| Cancel·lar reserves (pròpies) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Cancel·lar reserves (totes) | ✅ | ❌ | ❌ | ❌ | ❌ |
| Marcar ocupació | ✅ | ✅ | ✅ | ✅ | ❌ |
| Consultar històric | ✅ | ✅ | ✅ | ✅ | ✅ |

## 🗂️ Base de Dades

### Taules principals:
- **usuario**: Informació d'usuaris i rols
- **sala**: Definició de sales
- **mesa**: Meses amb estat i capacitat
- **reserva**: Reserves amb franges horàries
- **historico**: Registre d'ocupació de taules

### Relacions:
- Cada mesa pertany a una sala
- Cada reserva està vinculada a una mesa i sala
- L'històric registra qui va ocupar cada mesa

## 🎨 Característiques Tècniques

- **Backend**: PHP (PDO per a base de dades)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Base de dades**: MySQL
- **Arquitectura**: MVC simplificat
- **Seguretat**: 
  - Sessions PHP
  - Validació de permisos per rol
  - Prepared statements (prevenció SQL injection)
  - Validació de formularis client i servidor

## 📝 Notes Importants

1. **Franges horàries**: Les reserves es gestionen en intervals de 2 hores (08:00-10:00, 10:00-12:00, etc.)

2. **Sincronització d'estat**: L'estat de les meses es sincronitza automàticament basant-se en l'històric i reserves actives

3. **Imatges de sales**: Les sales temàtiques utilitzen imatges de les regions Pokémon ubicades a `img/regiones/`

4. **Filtre acumulatiu**: Els filtres de data, franja horària i cadires funcionen de manera acumulativa per refinar la cerca

5. **Validacions**: El sistema valida:
   - Format de DNI
   - Format de telèfon
   - Solapament de reserves
   - Disponibilitat de meses

## 🐛 Solució de Problemes

### Error de connexió a la base de dades
- Verificar credencials a `database/conexion.php`
- Assegurar-se que el servei MySQL està actiu

### Error 404 al accedir
- Verificar la ruta del projecte al servidor
- Comprovar que s'accedeix des de `pages/login.php`

### Les reserves no es mostren
- Verificar que la data seleccionada és igual o posterior a avui
- Comprobar que hi ha meses a la sala seleccionada

## 📧 Contacte

Projecte desenvolupat per al mòdul de Desenvolupament d'Aplicacions Web (DAW2).

---

**Versió**: 1.0  
**Data**: Desembre 2025
