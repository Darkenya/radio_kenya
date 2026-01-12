<?php
// admin.php
session_start();
// Check if admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get the user's full name from the session for display
$admin_full_name = $_SESSION['admin_full_name'] ?? 'Admin User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Radio Kenya</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
            --success: #48bb78;
            --danger: #f56565;
            --dark: #1a202c;
            --gray: #718096;
            --light: #f7fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: relative; /* Added for absolute positioning of logout button */
        }

        .header h1 {
            font-size: 2.5em;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .header p {
            color: var(--gray);
            font-size: 1.1em;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .tab {
            flex: 1;
            padding: 15px;
            background: white;
            border: none;
            border-radius: 15px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            color: var(--gray);
        }

        .tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .panel {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .panel.active {
            display: block;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1em;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(72, 187, 120, 0.4);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(245, 101, 101, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 2px solid #48bb78;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 2px solid #f56565;
        }

        .stations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .stations-table th,
        .stations-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .stations-table th {
            background: #f7fafc;
            font-weight: 600;
            color: var(--dark);
        }

        .stations-table tr:hover {
            background: #f7fafc;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-active {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-inactive {
            background: #fed7d7;
            color: #742a2a;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 0.9em;
        }

        .logo-upload-container {
            margin-top: 10px;
        }

        .logo-preview {
            width: 100px;
            height: 100px;
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            background: #f7fafc;
            overflow: hidden;
        }

        .logo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .color-picker {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .color-option {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
        }

        .color-option:hover {
            transform: scale(1.1);
            border-color: var(--dark);
        }

        .color-option.selected {
            border-color: var(--dark);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .tabs {
                flex-direction: column;
            }

            .stations-table {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="position: relative;">
            <h1>📻 Radio Kenya Admin</h1>
            <p>Welcome, **<?php echo htmlspecialchars($admin_full_name); ?>**! Manage your radio stations database.</p>
            <a href="logout.php" class="btn btn-danger" style="position: absolute; top: 30px; right: 30px; display: flex; align-items: center; gap: 5px; padding: 10px 20px;">🚪 Logout</a>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab(event, 'add')">➕ Add Station</button>
            <button class="tab" onclick="switchTab(event, 'manage')">📋 Manage Stations</button>
        </div>

        <div id="addPanel" class="panel active">
            <h2 style="margin-bottom: 30px; color: var(--dark);">Add New Station</h2>
            
            <div id="addAlert" class="alert"></div>

            <form id="addStationForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Station Name *</label>
                        <input type="text" name="name" required placeholder="e.g., Capital FM">
                    </div>
                    <div class="form-group">
                        <label>Frequency *</label>
                        <input type="text" name="frequency" required placeholder="e.g., 98.4 FM">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Genre *</label>
                        <select name="genre" required>
                            <option value="">Select Genre</option>
                            <option value="Pop">Pop</option>
                            <option value="Hip Hop">Hip Hop</option>
                            <option value="Rock">Rock</option>
                            <option value="Gospel">Gospel</option>
                            <option value="Classic">Classic</option>
                            <option value="Jazz">Jazz</option>
                            <option value="Reggae">Reggae</option>
                            <option value="Afrobeat">Afrobeat</option>
                            <option value="Country">Country</option>
                            <option value="Talk">Talk Radio</option>
                            <option value="News">News</option>
                            <option value="Sports">Sports</option>
                            <option value="Mix">Mix</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>County *</label>
                        <select name="city" required>
                            <option value="">Select County</option>
                            <option value="Baringo">Baringo</option>
                            <option value="Bomet">Bomet</option>
                            <option value="Bungoma">Bungoma</option>
                            <option value="Busia">Busia</option>
                            <option value="Calibri">Calibri</option>
                            <option value="Embu">Embu</option>
                            <option value="Garissa">Garissa</option>
                            <option value="Homa Bay">Homa Bay</option>
                            <option value="Isiolo">Isiolo</option>
                            <option value="Kajiado">Kajiado</option>
                            <option value="Kakamega">Kakamega</option>
                            <option value="Kamba">Kamba</option>
                            <option value="Kericho">Kericho</option>
                            <option value="Kiambu">Kiambu</option>
                            <option value="Kilifi">Kilifi</option>
                            <option value="Kirinyaga">Kirinyaga</option>
                            <option value="Kisii">Kisii</option>
                            <option value="Kisumu">Kisumu</option>
                            <option value="Kitui">Kitui</option>
                            <option value="Kwale">Kwale</option>
                            <option value="Laikipia">Laikipia</option>
                            <option value="Lamu">Lamu</option>
                            <option value="Machakos">Machakos</option>
                            <option value="Makueni">Makueni</option>
                            <option value="Mandera">Mandera</option>
                            <option value="Marsabit">Marsabit</option>
                            <option value="Mbeere">Mbeere</option>
                            <option value="Meru">Meru</option>
                            <option value="Migori">Migori</option>
                            <option value="Mombasa">Mombasa</option>
                            <option value="Murang'a">Murang'a</option>
                            <option value="Nairobi">Nairobi</option>
                            <option value="Nakuru">Nakuru</option>
                            <option value="Nandi">Nandi</option>
                            <option value="Narok">Narok</option>
                            <option value="Nyamira">Nyamira</option>
                            <option value="Nyandarua">Nyandarua</option>
                            <option value="Nyeri">Nyeri</option>
                            <option value="Samburu">Samburu</option>
                            <option value="Siaya">Siaya</option>
                            <option value="Taita-Taveta">Taita-Taveta</option>
                            <option value="Tana River">Tana River</option>
                            <option value="Tharaka">Tharaka</option>
                            <option value="Trans Nzoia">Trans Nzoia</option>
                            <option value="Turkana">Turkana</option>
                            <option value="Uasin Gishu">Uasin Gishu</option>
                            <option value="Vihiga">Vihiga</option>
                            <option value="Wajir">Wajir</option>
                            <option value="West Pokot">West Pokot</option>
                            <option value="Baringo">Baringo</option>
                             <option value="Kitale">Kitale</option>
<option value="Bomet">Bomet</option>
<option value="Bungoma">Bungoma</option>
<option value="Busia">Busia</option>
<option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
<option value="Embu">Embu</option>
<option value="Garissa">Garissa</option>
<option value="Homa Bay">Homa Bay</option>
<option value="Isiolo">Isiolo</option>
<option value="Tharaka-Nithi">Tharaka-Nithi</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Stream URL *</label>
                    <input type="url" name="stream_url" required placeholder="https://stream.example.com/radio.mp3">
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" required placeholder="Enter station description..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Station Logo *</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="logoFileInput" name="logo_file" accept="image/*" onchange="previewLogo(event)">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('logoFileInput').click()">📤 Upload Logo</button>
                        </div>
                        <div class="logo-preview" id="logoPreview">
                            <span style="color: #cbd5e0;">No image selected</span>
                        </div>
                        <input type="hidden" id="logoData" name="logo" value="">
                    </div>
                    <div class="form-group">
                        <label>Color Theme</label>
                        <input type="text" id="colorInput" name="color" value="#667eea" readonly>
                        <div class="color-picker">
                            <div class="color-option selected" style="background: #667eea;" onclick="selectColor(event, '#667eea')"></div>
                            <div class="color-option" style="background: #f56565;" onclick="selectColor(event, '#f56565')"></div>
                            <div class="color-option" style="background: #48bb78;" onclick="selectColor(event, '#48bb78')"></div>
                            <div class="color-option" style="background: #ed8936;" onclick="selectColor(event, '#ed8936')"></div>
                            <div class="color-option" style="background: #9f7aea;" onclick="selectColor(event, '#9f7aea')"></div>
                            <div class="color-option" style="background: #38b2ac;" onclick="selectColor(event, '#38b2ac')"></div>
                            <div class="color-option" style="background: #ec4899;" onclick="selectColor(event, '#ec4899')"></div>
                            <div class="color-option" style="background: #3b82f6;" onclick="selectColor(event, '#3b82f6')"></div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">✅ Add Station</button>
                    <button type="reset" class="btn" style="background: #e2e8f0; color: var(--dark);" onclick="resetForm()">🔄 Reset Form</button>
                </div>
            </form>
        </div>

        <div id="managePanel" class="panel">
            <h2 style="margin-bottom: 30px; color: var(--dark);">Manage Stations</h2>
            
            <div id="manageAlert" class="alert"></div>

            <div style="margin-bottom: 20px;">
                <input type="text" id="searchStations" placeholder="Search stations..." style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1em;" oninput="filterStations()">
            </div>

            <div style="overflow-x: auto;">
                <table class="stations-table" id="stationsTable">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Frequency</th>
                            <th>Genre</th>
                            <th>County</th>
                            <th>Listeners</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="stationsTableBody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">Loading stations...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API_URL = 'admin_api.php';
        let allStations = [];

        function switchTab(event, tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            
            event.target.classList.add('active');
            
            if (tab === 'add') {
                document.getElementById('addPanel').classList.add('active');
            } else {
                document.getElementById('managePanel').classList.add('active');
                loadStations();
            }
        }

        function previewLogo(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logoPreview');
                preview.innerHTML = `<img src="${e.target.result}" alt="Logo preview">`;
                document.getElementById('logoData').value = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function selectColor(event, color) {
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            event.target.classList.add('selected');
            document.getElementById('colorInput').value = color;
        }

        function resetForm() {
            document.getElementById('addStationForm').reset();
            document.getElementById('logoPreview').innerHTML = '<span style="color: #cbd5e0;">No image selected</span>';
            document.getElementById('logoData').value = '';
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector('.color-option').classList.add('selected');
        }

        document.getElementById('addStationForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            if (!data.logo) {
                showAlert('addAlert', 'Please upload a logo image', 'error');
                return;
            }
            
            try {
                const response = await fetch(API_URL + '?action=add_station', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.status === 401) {
                    showAlert('addAlert', 'Session expired. Redirecting to login...', 'error');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                    return;
                }

                if (result.success) {
                    showAlert('addAlert', 'Station added successfully!', 'success');
                    resetForm();
                } else {
                    showAlert('addAlert', 'Error: ' + (result.error || 'Failed to add station'), 'error');
                }
            } catch (error) {
                showAlert('addAlert', 'Error: ' + error.message, 'error');
            }
        });

        async function loadStations() {
            try {
                const response = await fetch(API_URL + '?action=get_all_stations');
                
                if (response.status === 401) {
                    document.getElementById('stationsTableBody').innerHTML = 
                        '<tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--danger);">Session expired. Redirecting to login...</td></tr>';
                    setTimeout(() => window.location.href = 'login.php', 2000);
                    return;
                }

                allStations = await response.json();
                displayStations(allStations);
            } catch (error) {
                console.error('Error loading stations:', error);
                document.getElementById('stationsTableBody').innerHTML = 
                    '<tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--danger);">Error loading stations</td></tr>';
            }
        }

        function displayStations(stations) {
            const tbody = document.getElementById('stationsTableBody');
            
            if (!stations || stations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px;">No stations found</td></tr>';
                return;
            }
            
            tbody.innerHTML = stations.map(station => `
                <tr>
                    <td style="font-size: 2em;"><img src="${station.logo}" style="width: 50px; height: 50px; object-fit: contain;" alt="${station.name}"></td>
                    <td><strong>${station.name}</strong></td>
                    <td>${station.frequency}</td>
                    <td>${station.genre}</td>
                    <td>${station.city}</td>
                    <td>${station.listeners || 0}</td>
                    <td>
                        <span class="badge ${station.is_active == 1 ? 'badge-active' : 'badge-inactive'}">
                            ${station.is_active == 1 ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-small btn-${station.is_active == 1 ? 'danger' : 'success'}" onclick="toggleStatus(${station.id}, ${station.is_active})">
                                ${station.is_active == 1 ? '❌ Deactivate' : '✅ Activate'}
                            </button>
                            <button class="btn btn-small btn-danger" onclick="deleteStation(${station.id}, '${station.name.replace(/'/g, "\\'")}')">
                                🗑️ Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function filterStations() {
            const search = document.getElementById('searchStations').value.toLowerCase();
            const filtered = allStations.filter(station => 
                station.name.toLowerCase().includes(search) ||
                station.genre.toLowerCase().includes(search) ||
                station.city.toLowerCase().includes(search)
            );
            displayStations(filtered);
        }

        async function toggleStatus(id, currentStatus) {
            if (!confirm('Are you sure you want to change this station\'s status?')) return;
            
            try {
                const response = await fetch(API_URL + '?action=toggle_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                
                if (response.status === 401) {
                    showAlert('manageAlert', 'Session expired. Redirecting to login...', 'error');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                    return;
                }

                const result = await response.json();
                
                if (result.success) {
                    showAlert('manageAlert', 'Station status updated!', 'success');
                    loadStations();
                } else {
                    showAlert('manageAlert', 'Error updating status', 'error');
                }
            } catch (error) {
                showAlert('manageAlert', 'Error: ' + error.message, 'error');
            }
        }

        async function deleteStation(id, name) {
            if (!confirm(`Are you sure you want to DELETE "${name}"? This cannot be undone!`)) return;
            
            try {
                const response = await fetch(API_URL + '?action=delete_station', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                if (response.status === 401) {
                    showAlert('manageAlert', 'Session expired. Redirecting to login...', 'error');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('manageAlert', 'Station deleted successfully!', 'success');
                    loadStations();
                } else {
                    showAlert('manageAlert', 'Error deleting station', 'error');
                }
            } catch (error) {
                showAlert('manageAlert', 'Error: ' + error.message, 'error');
            }
        }

        function showAlert(elementId, message, type) {
            const alert = document.getElementById(elementId);
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>