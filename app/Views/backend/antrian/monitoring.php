<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?? 'Monitoring Antrian' ?> | Munaqosah</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('template/backend/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('template/backend/dist/css/adminlte.min.css') ?>">
    
    <style>
        .small-box .icon > i { top: 10px; right: 10px; font-size: 60px; opacity: 0.3; }
        .card-antrian { border-left: 5px solid #007bff; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .card-antrian.active { border-left-color: #28a745; background-color: #f0fff4; }
        .antrian-list-item { 
            border-bottom: 1px solid #eee; 
            padding: 10px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            transition: background 0.3s;
        }
        .antrian-list-item:hover { background: #f9f9f9; }
        .antrian-no { 
            font-size: 1.8rem; /* Slightly larger for readability */
            font-weight: bold; 
            background: #555; 
            color: #fff; 
            width: 70px;     /* Fixed Width */
            height: 60px;    /* Fixed Height */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px; 
            text-align: center;
            white-space: nowrap; /* Prevent wrapping */
            padding: 0;      /* Remove padding, use flex centering */
            flex-shrink: 0;  /* Prevent shrinking */
        }
        .antrian-info { margin-left: 15px; flex-grow: 1; }
        .antrian-name { font-weight: bold; font-size: 1rem; display: block; }
        .antrian-group { font-size: 0.8rem; color: #666; }
        .status-badge { font-size: 0.8rem; padding: 5px 10px; border-radius: 15px; text-transform: uppercase; font-weight: bold; }
        .status-badge.selesai { background: #d4edda; color: #155724; }
        .status-badge.dipanggil { background-color: #fd7e14; color: white; } /* Orange */
        .status-badge.sedang_ujian { background: #f8d7da; color: #721c24; } /* Red */
        .status-badge.menunggu { background: #fff3cd; color: #856404; } /* Yellow */
        
        /* Custom border/text for room cards */
        /* Custom border/text for room cards */
        .border-orange { border-color: #fd7e14 !important; }
        .badge-orange { background-color: #fd7e14; color: white; }
        
        .antrian-avatar-placeholder {
            width: 45px;
            height: 60px; /* 3:4 Aspect Ratio */
            border-radius: 4px;
            background-color: #6c757d;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 0 10px;
        }

        /* Override BG classes for number badge specifically if needed, or rely on .status-badge.* */
        .antrian-no {
            /* Inherits bg from status-badge classes */
            color: #fff !important; /* Ensure text is white */
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .progress-group .progress-text { font-weight: 600; }
        .progress-group .progress-number { float: right; }

        /* Custom Colors matching Ref */
        .bg-total { background-color: #17a2b8 !important; color: white; }
        .bg-success { background-color: #28a745 !important; color: white; }
        .bg-warning { background-color: #ffc107 !important; color: #1f2d3d; }
        .bg-primary { background-color: #007bff !important; color: white; }
        
        /* Solid Backgrounds for Number Badge */
        .bg-status-selesai { background-color: #28a745 !important; color: white !important; }
        .bg-status-menunggu { background-color: #ffc107 !important; color: #1f2d3d !important; } /* Yellow with dark text */
        .bg-status-dipanggil { background-color: #fd7e14 !important; color: white !important; }
        .bg-status-sedang_ujian { background-color: #dc3545 !important; color: white !important; }

        #antrian-list-container {
            max-height: 80vh;
            overflow-y: auto;
        }
        .antrian-avatar {
            width: 45px;
            height: 60px; /* 3:4 Aspect Ratio */
            border-radius: 4px; 
            object-fit: cover;
            margin: 0 10px;
            border: 2px solid #ddd;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white shadow-sm border-bottom-0">
    <div class="container-fluid">
      <a href="#" class="navbar-brand">
        <span class="brand-text font-weight-bold">Monitoring Status Antrian</span>
      </a>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
         <ul class="navbar-nav ml-auto">
             <li class="nav-item d-flex align-items-center">
                 <span class="text-muted mr-2" id="current-day"></span>
                 <span class="font-weight-bold" id="current-time"></span>
             </li>
             <li class="nav-item ml-3">
                 <button class="btn btn-sm btn-outline-primary" id="btn-refresh" onclick="loadQueueData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                 </button>
             </li>
         </ul>
      </div>
    </div>
  </nav>

  <!-- Content Wrapper -->
  <div class="content-wrapper bg-light">
    <div class="content pt-3">
      <div class="container-fluid">
          
        <!-- Info Grafis (Global Stats) -->
        <div class="row mb-3">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="small-box bg-total">
                    <div class="inner">
                        <h3 id="stat-total">0</h3>
                        <p>Total Peserta</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="stat-selesai">0</h3>
                        <p>Sudah Diuji</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="stat-ujian">0</h3>
                        <p>Sedang Ujian / Menunggu</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3 id="stat-progress">0<sup style="font-size: 20px">%</sup></h3>
                        <p>Progress Total</p>
                    </div>
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Daftar Antrian (Live Feed) -->
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title font-weight-bold">Daftar Antrian</h3>
                    </div>
                    <div class="card-body p-0" id="antrian-list-container">
                        <!-- List loaded via JS -->
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Monitoring per Grup -->
            <div class="col-lg-8 col-md-7">
                <!-- NEW: Room Status Section (Global/Filtered) -->
                <div id="room-status-header" class="d-none mb-2">
                    <h5 class="text-muted"><i class="fas fa-door-open mr-1"></i> Status Ruangan</h5>
                </div>
                <div id="room-status-main" class="row mb-4"></div>

                <h5 class="mb-3 text-muted">Monitoring Antrian per Grup Materi</h5>
                <div class="row" id="grup-container">
                    <!-- Cards loaded via JS -->
                </div>
            </div>
        </div>

      </div><!-- /.container-fluid -->
    </div>
  </div>
    
  <footer class="main-footer border-top-0 text-sm">
    <div class="float-right d-none d-sm-inline">
      Munaqosah System
    </div>
    <strong>Copyright &copy; <?= date('Y') ?> SDIT An-Nahl.</strong>
  </footer>
</div>

<!-- SCRIPTS -->
<script src="<?= base_url('template/backend/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('template/backend/dist/js/adminlte.min.js') ?>"></script>

<script>
    const baseUrl = '<?= base_url() ?>'; // Define base url for JS usage
    // --- Clock ---
    function updateTime() {
        const now = new Date();
        const optionsDay = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
        
        document.getElementById('current-day').innerText = now.toLocaleDateString('id-ID', optionsDay);
        document.getElementById('current-time').innerText = now.toLocaleTimeString('id-ID', optionsTime);
    }
    setInterval(updateTime, 1000);
    updateTime();

    // --- Main Logic ---
    function loadQueueData() {
        // Forward query params (filter grup) to API
        const queryParams = window.location.search;
        
        $.ajax({
            url: '<?= base_url('backend/antrian/get-queue-data') ?>' + queryParams,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    renderDashboard(response.data);
                } else {
                    console.error('API Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
               console.error('Connection Error:', error);
            }
        });
    }

    function renderDashboard(data) {
        const queues = data.queues;
        const rooms = data.rooms || {}; // Get room data
        const globalCounts = data.globalStats; 
        const groupMetadata = data.groupMetadata || []; // Receive group metadata from Controller
        
        const urlParams = new URLSearchParams(window.location.search);
        const filterGrupId = urlParams.get('grup');

        let total = 0;
        let done = 0;
        let active = 0;
        let waiting = 0;

        // Calculate stats from queues based on filter
        Object.keys(queues).forEach(groupId => {
             // If filter exists, only count that group for the Top Stats
             // If no filter, count all
             if (filterGrupId && filterGrupId != groupId) return;
             
             const groupQueue = queues[groupId];
             total += groupQueue.length;
             done += groupQueue.filter(q => q.status_antrian == 'selesai').length;
             active += groupQueue.filter(q => q.status_antrian == 'sedang_ujian' || q.status_antrian == 'dipanggil').length;
             waiting += groupQueue.filter(q => q.status_antrian == 'menunggu').length;
        });

        const progress = total > 0 ? Math.round((done / total) * 100) : 0;
        
        $('#stat-total').text(total);
        $('#stat-selesai').text(done);
        $('#stat-ujian').text(active + waiting); 
        $('#stat-progress').html(progress + '<sup style="font-size: 20px">%</sup>');

        // --- NEW: Render Main Room Status Section ---
        let mainRoomHtml = '';
        let hasRooms = false;
        let totalRooms = 0;
        let visibleRooms = [];

        // 1. First Pass: Collect all visible rooms to count them
        Object.keys(rooms).forEach(groupId => {
            if (filterGrupId && filterGrupId != groupId) return; // Follow filter
            const groupRooms = rooms[groupId];
            if(groupRooms && groupRooms.length > 0) {
                 groupRooms.forEach(room => {
                     visibleRooms.push(room);
                 });
            }
        });

        // 2. Determine Column Size
        totalRooms = visibleRooms.length;
        let colClass = 'col-md-4'; // Default (3 per row)
        if (totalRooms === 1) colClass = 'col-md-12';
        else if (totalRooms === 2) colClass = 'col-md-6';
        
        // 3. Render Rooms
        if (totalRooms > 0) {
             hasRooms = true;
             visibleRooms.forEach(room => {
                let cardClass = 'bg-success'; // Default empty
                let badgeIcon = '<i class="fas fa-door-open mr-1"></i>';
                let badgeText = 'Kosong';
                let badgeClass = 'text-success';
                let textClass = 'text-white';
                
                let occupantText = 'Kosong';
                let photoHtml = '';

                const isActive = room.is_active && room.occupant;
                
                if (isActive) {
                    const p = room.occupant;
                    const status = p.status_antrian;
                    
                    occupantText = `Peserta: <b>${p.no_peserta}</b> - ${p.NamaSiswa || p.nama_siswa || ''}`;

                    // Photo Logic
                    let photoUrl = '';
                    if (p.Foto || p.foto) {
                        photoUrl = baseUrl + '/' + (p.Foto || p.foto);
                    } else {
                        const name = p.NamaSiswa || p.nama_siswa || 'U';
                        photoUrl = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=random&color=fff&bold=true&length=2';
                    }
                    
                    photoHtml = `<img src="${photoUrl}" class="img-circle elevation-2 mr-2" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid white;">`;

                    if (status == 'dipanggil') {
                        cardClass = 'bg-warning';
                        badgeIcon = '<i class="fas fa-bullhorn mr-1"></i>';
                        badgeText = 'Memanggil';
                        badgeClass = 'text-warning';
                        textClass = 'text-dark';
                    } else if (status == 'sedang_ujian') {
                        cardClass = 'bg-danger';
                        badgeIcon = '<i class="fas fa-users mr-1"></i>';
                        badgeText = 'Penuh';
                        badgeClass = 'text-danger';
                    }
                }

                mainRoomHtml += `
                <div class="${colClass} mb-2">
                    <div class="card ${cardClass}" style="min-height: 100px;">
                        <div class="card-body pt-2 pl-2 pr-2 pb-2">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="font-weight-bold m-0 ${textClass}">${room.room_name}</h5>
                                <span class="badge badge-light ${badgeClass}">${badgeIcon} ${badgeText}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                 ${photoHtml}
                                 <p class="${textClass} mb-0" style="font-size: 0.9rem; line-height: 1.2;">${occupantText}</p>
                            </div>
                        </div>
                    </div>
                </div>
                `;
             });
        }

        if(hasRooms) {
            $('#room-status-header').removeClass('d-none');
            $('#room-status-main').html(mainRoomHtml);
        } else {
            $('#room-status-header').addClass('d-none');
            $('#room-status-main').empty();
        }


        // 2. Render Grup Cards (Existing Logic, Condensed)
        let grupHtml = '';
        let allParticipants = []; 

        Object.keys(queues).forEach(groupId => {
             const groupQueue = queues[groupId];
             if(groupQueue.length > 0) allParticipants = allParticipants.concat(groupQueue);
             
             const gTotal = groupQueue.length;
             const gDone = groupQueue.filter(q => q.status_antrian == 'selesai').length;
             const gActive = groupQueue.filter(q => q.status_antrian == 'sedang_ujian' || q.status_antrian == 'dipanggil').length;
             const gWaiting = groupQueue.filter(q => q.status_antrian == 'menunggu').length;
             const gProgress = gTotal > 0 ? Math.round((gDone / gTotal) * 100) : 0;
             
             // Lookup Name from Metadata (or fallback to queue data, or ID)
             let groupName = 'Grup ' + groupId;
             const foundGroup = groupMetadata.find(g => g.id == groupId);
             if(foundGroup) groupName = foundGroup.nama_grup_materi;
             else if(groupQueue.length > 0 && groupQueue[0].NamaGrup) groupName = groupQueue[0].NamaGrup;
             
             const isActive = gActive > 0;
             const cardBorder = isActive ? 'border-left: 5px solid #28a745;' : 'border-left: 5px solid #007bff;';
             const activeBadge = isActive ? '<span class="badge badge-danger float-right">Sedang Ujian</span>' : (gWaiting == 0 && gDone > 0 ? '<span class="badge badge-success float-right">Selesai</span>' : '<span class="badge badge-secondary float-right">Idle</span>');

             grupHtml += `
             <div class="col-lg-4 col-md-6 mb-3">
                <div class="card shadow-sm" style="${cardBorder}">
                    <div class="card-header bg-white">
                        <h5 class="card-title font-weight-bold"><i class="fas fa-layer-group text-muted mr-2"></i> ${groupName}</h5>
                        ${activeBadge}
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-4 border-right">
                                <h5 class="font-weight-bold mb-0">${gTotal}</h5>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4 border-right">
                                <h5 class="font-weight-bold text-success mb-0">${gDone}</h5>
                                <small class="text-muted">Selesai</small>
                            </div>
                            <div class="col-4">
                                <h5 class="font-weight-bold text-warning mb-0">${gWaiting}</h5>
                                <small class="text-muted">Tunggu</small>
                            </div>
                        </div>
                        
                        <div class="progress-group">
                            <span class="progress-text">Progress Antrian</span>
                            <span class="progress-number"><b>${gDone}</b>/${gTotal}</span>
                            <div class="progress progress-sm">
                              <div class="progress-bar bg-primary" style="width: ${gProgress}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
             `;
        });
        
        if (grupHtml === '') {
            grupHtml = '<div class="col-12 text-center text-muted py-5">Belum ada grup yang aktif</div>';
        }
        $('#grup-container').html(grupHtml);

        // 3. Render Segmented List (Per Group)
        let listHtml = '';
        
        Object.keys(queues).forEach(groupId => {
             if (filterGrupId && filterGrupId != groupId) return;

             const groupQueue = queues[groupId];
             if(groupQueue.length > 0) {
                 const groupName = (groupQueue[0].NamaGrup || 'Grup') || ('Grup ' + groupId);
                 
                 listHtml += `
                    <div class="bg-light p-2 font-weight-bold border-bottom sticky-top" style="top:0; z-index:10;">
                        <span class="badge badge-primary mr-1">${groupName}</span>
                    </div>
                 `;
                 
                 groupQueue.sort((a, b) => {
                    const score = (status) => {
                        if(status === 'menunggu') return 3; 
                        if(status === 'sedang_ujian' || status === 'dipanggil') return 2;
                        return 1;
                    };
                    const sA = score(a.status_antrian);
                    const sB = score(b.status_antrian);
                    if(sA !== sB) return sB - sA; 
                    if (a.status_antrian === 'menunggu' && b.status_antrian === 'menunggu') return a.id - b.id; 
                    return new Date(b.updated_at) - new Date(a.updated_at);
                 });

                 groupQueue.forEach(p => {
                    let badgeClass = '';
                    let label = '';
                    
                    if(p.status_antrian === 'selesai') { badgeClass = 'selesai'; label = 'Selesai'; }
                    else if(p.status_antrian === 'menunggu') { badgeClass = 'menunggu'; label = 'Menunggu'; }
                    else if(p.status_antrian === 'dipanggil') { badgeClass = 'dipanggil'; label = 'Dipanggil'; } 
                    else { badgeClass = 'sedang_ujian'; label = 'Sedang Ujian'; }
                    
                    const numBgClass = 'bg-status-' + badgeClass;

                    let avatarHtml = '';
                    if (p.Foto && p.Foto != '') {
                         let fotoUrl = baseUrl + '/' + p.Foto;
                         avatarHtml = `<img src="${fotoUrl}" class="antrian-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">`;
                         const name = p.NamaSiswa || p.NamaLengkap || 'User';
                         const initials = name.match(/\b\w/g) || [];
                         const avatarText = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
                         avatarHtml += `<div class="antrian-avatar-placeholder" style="display:none">${avatarText}</div>`;
                    } else {
                         const name = p.NamaSiswa || p.NamaLengkap || 'User';
                         const initials = name.match(/\b\w/g) || [];
                         const avatarText = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
                         avatarHtml = `<div class="antrian-avatar-placeholder">${avatarText}</div>`;
                    }
        
                    listHtml += `
                    <div class="antrian-list-item">
                        <div class="antrian-no ${numBgClass}">${p.no_peserta.split('-').pop()}</div> 
                        ${avatarHtml}
                        <div class="antrian-info">
                            <span class="antrian-name">${p.NamaSiswa || p.NamaLengkap}</span>
                            <small class="text-muted"><i class="fas fa-clock mr-1"></i> ${p.waktu_mulai ? p.waktu_mulai.split(' ')[1] : '-'} s/d ${p.waktu_selesai ? p.waktu_selesai.split(' ')[1] : '-'}</small>
                        </div>
                        <span class="status-badge ${badgeClass}">${label}</span>
                    </div>
                    `;
                 });
             }
        });
        
        if (listHtml === '') {
             listHtml = '<div class="text-center py-4 text-muted">Belum ada data antrian</div>';
        }
        $('#antrian-list-container').html(listHtml);
    }

    // Auto Refresh every 5s
    setInterval(loadQueueData, 5000);
    // Initial Load
    $(document).ready(function(){
        loadQueueData();
    });

</script>
</body>
</html>
