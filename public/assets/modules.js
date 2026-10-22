(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const tr = {
    en: {
      loadoutTitle:'Combat intelligence / Loadout planner', loadoutSub:'Mission-aware recommendations from a transparent rule set.',
      enemyFaction:'Enemy faction', missionType:'Mission type', playstyle:'Playstyle', difficulty:'Difficulty', squadSize:'Squad size', planetContext:'Planet context (optional)',
      generateLoadout:'Generate loadout', copyLoadout:'Copy plan', loadoutDisclaimer:'Recommendations are heuristic suggestions based on tags and mission context—not live win-rate statistics, a guarantee, or an official build.',
      communityTitle:'Community intel / Operational picture', communitySub:'Derived only from currently synchronized public feeds.', alertsTitle:'Operational alerts', alertsSub:'Browser notifications and optional server push when configured.',
      browserNotifications:'Browser notifications', enableAlerts:'Enable alerts', disablePush:'Disable push', alertOrders:'New major orders', alertOrdersSub:'Notify when an order changes after initial sync.',
      alertDispatches:'New dispatches', alertDispatchesSub:'Notify when a new galactic dispatch arrives.', alertAttacks:'New planetary attacks', alertAttacksSub:'Notify when a tracked planet becomes attacked.',
      alertsDisclaimer:'These are local browser alerts based on dashboard polling. True server push while the site is closed requires VAPID keys, subscription storage, and a push delivery service.',
      archivesTitle:'Galactic archives / Timeline', archivesSub:'Dispatches, official news, orders and current planetary events.', catalogTitle:'Field manual / Equipment & enemy catalog',
      catalogSub:'Searchable reference catalog; recommendations are tagged, not live game telemetry.', catalogDisclaimer:'Catalog entries are a maintained starter reference. Unlock availability, balance, and patch changes should be verified in-game.',
      searchArchive:'Search archive...', searchCatalog:'Search weapons, armor, stratagems or enemies...', allRecords:'All records', dispatches:'Dispatches', officialNews:'Official news', orders:'Orders', planetaryEvents:'Planetary events', history:'Historical snapshots',
      generated:'Loadout generated.', copied:'Plan copied.', copyError:'Could not copy automatically; select and copy the plan.', noMatches:'No entries match your search.', waiting:'Waiting for synchronized data.',
      notificationUnsupported:'This browser does not support notifications.', notificationDenied:'Notification permission was denied in browser settings.', notificationsEnabled:'Browser alerts enabled. Keep this page open to receive updates.',
      notificationsDisabled:'Notifications are disabled.', noData:'No synchronized data available yet.', attackPlanets:'Planets under attack', activeEvents:'Planets with active events', trackedPlanets:'Tracked planets', campaigns:'Campaigns reported',
      topAttacks:'Current planetary pressure', topEvents:'Active planetary events', noEvents:'No active planetary events in the current feed.',
      fourSlots:'Four recommended stratagem slots', team:'Team utility', supportWeapon:'Support weapon', orbital:'Orbital / heavy damage', defenseSlot:'Defense / crowd control',
      tags:'Tags', faction:'Faction', role:'Role', utility:'Utility', notes:'Operational note', event:'Event', date:'Date',
      orderChanged:'Major order update', newDispatch:'New galactic dispatch', planetAttacked:'Planet under attack',
      balanced:'Balanced', antiarmor:'Anti-armor', crowd:'Crowd control', support:'Support / team utility', stealth:'Stealth / mobility',
      terminids:'Terminids', automatons:'Automatons', illuminate:'Illuminate', unknown:'Unknown / mixed',
      eradicate:'Eradicate / high kills', defense:'Defense / hold position', objectives:'Objectives / operations', recon:'Recon / stealth', extermination:'Heavy targets', escort:'Escort / civilians',
      solo:'Solo', duo:'Duo', squad:'Full squad', noPlanet:'No planet selected',
      copyUnsupported:'Clipboard access is unavailable in this context.'
    },
    es: {
      loadoutTitle:'Inteligencia de combate / Planificador de equipamiento', loadoutSub:'Recomendaciones según misión con reglas transparentes.',
      enemyFaction:'Facción enemiga', missionType:'Tipo de misión', playstyle:'Estilo de juego', difficulty:'Dificultad', squadSize:'Tamaño del escuadrón', planetContext:'Planeta (opcional)',
      generateLoadout:'Generar equipamiento', copyLoadout:'Copiar plan', loadoutDisclaimer:'Son sugerencias heurísticas basadas en etiquetas y contexto, no estadísticas de victorias en vivo, garantías ni una configuración oficial.',
      communityTitle:'Inteligencia comunitaria / Situación operativa', communitySub:'Calculada únicamente con los feeds públicos sincronizados.', alertsTitle:'Alertas operativas', alertsSub:'Notificaciones del navegador y push del servidor si está configurado.',
      browserNotifications:'Notificaciones del navegador', enableAlerts:'Activar alertas', disablePush:'Desactivar push', alertOrders:'Nuevas órdenes principales', alertOrdersSub:'Avisar cuando una orden cambie después de la sincronización inicial.',
      alertDispatches:'Nuevos comunicados', alertDispatchesSub:'Avisar cuando llegue un nuevo comunicado galáctico.', alertAttacks:'Nuevos ataques planetarios', alertAttacksSub:'Avisar cuando un planeta pase a estar bajo ataque.',
      alertsDisclaimer:'Son alertas locales basadas en la consulta periódica del panel. Las notificaciones push reales con el sitio cerrado requieren claves VAPID, guardar suscripciones y un servicio de entrega.',
      archivesTitle:'Archivos galácticos / Cronología', archivesSub:'Comunicados, noticias oficiales, órdenes y eventos planetarios actuales.', catalogTitle:'Manual de campo / Catálogo de equipo y enemigos',
      catalogSub:'Catálogo de referencia con búsqueda; las recomendaciones son etiquetas, no telemetría en vivo.', catalogDisclaimer:'El catálogo es una referencia inicial mantenida manualmente. Verifica dentro del juego desbloqueos, balance y cambios de parche.',
      searchArchive:'Buscar en archivos...', searchCatalog:'Buscar armas, armaduras, estratagemas o enemigos...', allRecords:'Todos los registros', dispatches:'Comunicados', officialNews:'Noticias oficiales', orders:'Órdenes', planetaryEvents:'Eventos planetarios', history:'Capturas históricas',
      generated:'Equipamiento generado.', copied:'Plan copiado.', copyError:'No se pudo copiar automáticamente; selecciona y copia el plan.', noMatches:'No hay resultados para esa búsqueda.', waiting:'Esperando datos sincronizados.',
      notificationUnsupported:'Este navegador no admite notificaciones.', notificationDenied:'Se denegó el permiso de notificaciones en el navegador.', notificationsEnabled:'Alertas activadas. Mantén esta página abierta para recibir avisos.',
      notificationsDisabled:'Las notificaciones están desactivadas.', noData:'Todavía no hay datos sincronizados.', attackPlanets:'Planetas bajo ataque', activeEvents:'Planetas con eventos activos', trackedPlanets:'Planetas monitorizados', campaigns:'Campañas reportadas',
      topAttacks:'Presión planetaria actual', topEvents:'Eventos planetarios activos', noEvents:'No hay eventos planetarios activos en el feed actual.',
      fourSlots:'Cuatro estratagemas recomendadas', team:'Utilidad de equipo', supportWeapon:'Arma de apoyo', orbital:'Daño orbital / pesado', defenseSlot:'Defensa / control de masas',
      tags:'Etiquetas', faction:'Facción', role:'Rol', utility:'Utilidad', notes:'Nota operativa', event:'Evento', date:'Fecha',
      orderChanged:'Actualización de orden principal', newDispatch:'Nuevo comunicado galáctico', planetAttacked:'Planeta bajo ataque',
      balanced:'Equilibrado', antiarmor:'Antiblindaje', crowd:'Control de masas', support:'Apoyo / utilidad de equipo', stealth:'Sigilo / movilidad',
      terminids:'Terminidos', automatons:'Autómatas', illuminate:'Iluminados', unknown:'Desconocida / mixta',
      eradicate:'Erradicación / muchas bajas', defense:'Defensa / mantener posición', objectives:'Objetivos / operaciones', recon:'Reconocimiento / sigilo', extermination:'Objetivos pesados', escort:'Escolta / civiles',
      solo:'Solo', duo:'Dúo', squad:'Escuadrón completo', noPlanet:'Ningún planeta seleccionado',
      copyUnsupported:'El portapapeles no está disponible en este contexto.'
    }
  };
  let lang = localStorage.getItem('gc-lang') === 'es' ? 'es' : 'en';
  const t = key => (tr[lang] && tr[lang][key]) || (tr.en[key]) || key;
  const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const plain = value => String(value ?? '').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi,' ').replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi,' ').replace(/<[^>]*>/g,' ').replace(/&nbsp;/gi,' ').replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&quot;/gi,'"').replace(/&#39;|&apos;/gi,"'").replace(/\s+/g,' ').trim();
  const safeHref = value => { try { const u=new URL(String(value),window.location.origin); return ['https:','http:'].includes(u.protocol)?u.href:''; } catch (_) { return ''; } };
  const fmt = n => new Intl.NumberFormat(lang === 'es' ? 'es-PE' : 'en-US',{maximumFractionDigits:1}).format(Number(n)||0);
  const catalog = {
    stratagems: [
      {name:'Eagle Airstrike',icon:'✈️',role:'area',factions:['terminids','automatons','illuminate'],tags:['crowd','objectives','balanced'],desc:'Flexible explosive strike for groups and structures.'},
      {name:'Eagle 500kg Bomb',icon:'💥',role:'heavy',factions:['terminids','automatons','illuminate'],tags:['antiarmor','extermination'],desc:'High burst damage against priority targets; placement matters.'},
      {name:'Orbital Precision Strike',icon:'☄️',role:'heavy',factions:['terminids','automatons','illuminate'],tags:['antiarmor','objectives','balanced'],desc:'Accurate heavy hit with timing and positioning requirements.'},
      {name:'Orbital Railcannon Strike',icon:'⚡',role:'heavy',factions:['terminids','automatons','illuminate'],tags:['antiarmor','extermination'],desc:'Automated priority-target strike with limited flexibility.'},
      {name:'Orbital Gatling Barrage',icon:'🌀',role:'area',factions:['terminids','automatons','illuminate'],tags:['crowd','defense'],desc:'Sustained area denial against clustered enemies.'},
      {name:'Orbital Gas Strike',icon:'☣️',role:'area',factions:['terminids','illuminate'],tags:['crowd','defense'],desc:'Area denial; coordinate with teammates around the gas cloud.'},
      {name:'Orbital EMS Strike',icon:'⚡',role:'control',factions:['terminids','automatons','illuminate'],tags:['support','defense','crowd'],desc:'Slows enemies and creates time to reposition or complete objectives.'},
      {name:'Orbital Smoke Strike',icon:'🌫️',role:'utility',factions:['automatons','illuminate'],tags:['stealth','support','objectives'],desc:'Breaks sight lines for movement and disengagement.'},
      {name:'Eagle Smoke Strike',icon:'🌫️',role:'utility',factions:['automatons','illuminate'],tags:['stealth','support','objectives'],desc:'Mobile smoke screen for crossings and extraction.'},
      {name:'Recoilless Rifle',icon:'🎯',role:'supportWeapon',factions:['terminids','automatons','illuminate'],tags:['antiarmor','extermination'],desc:'Team-reload capable anti-armor launcher; manage ammunition.'},
      {name:'Quasar Cannon',icon:'🔆',role:'supportWeapon',factions:['terminids','automatons','illuminate'],tags:['antiarmor','solo','balanced'],desc:'Reusable anti-armor beam with charge-up and cooldown.'},
      {name:'Autocannon',icon:'🔫',role:'supportWeapon',factions:['automatons','terminids','illuminate'],tags:['balanced','crowd','objectives'],desc:'Versatile medium/heavy target tool; backpack occupies a slot.'},
      {name:'Spear',icon:'🎯',role:'supportWeapon',factions:['automatons','terminids','illuminate'],tags:['antiarmor','extermination'],desc:'Long-range lock-on for large targets when line of sight permits.'},
      {name:'Machine Gun Sentry',icon:'🛡️',role:'defense',factions:['terminids','automatons','illuminate'],tags:['crowd','defense','solo'],desc:'Automated perimeter fire; place with friendly fire lanes in mind.'},
      {name:'Autocannon Sentry',icon:'🛡️',role:'defense',factions:['terminids','automatons','illuminate'],tags:['antiarmor','defense','extermination'],desc:'Heavy automated fire support for defensive positions.'},
      {name:'Rocket Sentry',icon:'🚀',role:'defense',factions:['terminids','automatons','illuminate'],tags:['antiarmor','defense'],desc:'Ranged fire support for armored threats; protect its line of fire.'},
      {name:'Shield Generator Relay',icon:'🫧',role:'utility',factions:['automatons','illuminate'],tags:['support','defense','objectives'],desc:'Temporary cover for exposed objectives and team repositioning.'},
      {name:'Supply Pack',icon:'🎒',role:'utility',factions:['terminids','automatons','illuminate'],tags:['support','solo','balanced'],desc:'Sustain ammunition and utility, especially in longer operations.'},
      {name:'Jump Pack',icon:'🪂',role:'mobility',factions:['terminids','automatons','illuminate'],tags:['stealth','solo','objectives'],desc:'Mobility tool for repositioning and alternate routes.'},
      {name:'Guard Dog Rover',icon:'🤖',role:'defense',factions:['terminids','illuminate'],tags:['solo','crowd','balanced'],desc:'Autonomous close defense; monitor friendly-fire risk.'},
      {name:'Anti-Personnel Minefield',icon:'💣',role:'area',factions:['terminids','automatons','illuminate'],tags:['defense','crowd'],desc:'Area denial for predictable approaches; hazardous to teammates.'},
      {name:'HMG Emplacement',icon:'🔩',role:'defense',factions:['terminids','automatons','illuminate'],tags:['defense','antiarmor'],desc:'Fixed heavy weapon for lanes that can be held safely.'}
    ],
    weapons: [
      {name:'AR-23 Liberator',icon:'🔫',role:'Assault rifle',tags:['balanced'],desc:'General-purpose primary with a straightforward handling profile.'},
      {name:'AR-23P Liberator Penetrator',icon:'🔫',role:'Penetrating rifle',tags:['antiarmor'],desc:'Trades some general-purpose performance for better armor penetration.'},
      {name:'SG-225 Breaker',icon:'💥',role:'Shotgun',tags:['crowd'],desc:'High close-range burst; manage reloads and engagement distance.'},
      {name:'SG-225IE Breaker Incendiary',icon:'🔥',role:'Incendiary shotgun',tags:['crowd','terminids'],desc:'Fire pressure is useful against swarms; watch friendly fire.'},
      {name:'PLAS-1 Scorcher',icon:'⚡',role:'Plasma rifle',tags:['balanced','automatons'],desc:'Explosive plasma projectiles; mind splash damage at close range.'},
      {name:'JAR-5 Dominator',icon:'🎯',role:'Explosive rifle',tags:['antiarmor','automatons'],desc:'Heavy-hitting primary with deliberate handling.'},
      {name:'LAS-16 Sickle',icon:'🔆',role:'Energy rifle',tags:['balanced'],desc:'Sustained fire with heat management instead of conventional magazines.'},
      {name:'SMG-37 Defender',icon:'🔫',role:'SMG',tags:['support','objectives'],desc:'One-handed handling can support objective interactions and carrying.'},
      {name:'P-19 Redeemer',icon:'🔫',role:'Machine pistol',tags:['backup','crowd'],desc:'Emergency close-range sidearm with fast ammunition consumption.'},
      {name:'GP-31 Grenade Pistol',icon:'💣',role:'Explosive sidearm',tags:['objectives'],desc:'Utility explosive sidearm for closing certain holes or destroying targets.'},
      {name:'R-36 Eruptor',icon:'💥',role:'Explosive rifle',tags:['antiarmor','objectives'],desc:'Explosive precision utility; keep safe distance from blast radius.'},
      {name:'CB-9 Exploding Crossbow',icon:'🏹',role:'Explosive crossbow',tags:['stealth','objectives'],desc:'Quieter ranged explosive utility; evaluate handling and blast radius.'}
    ],
    armor: [
      {name:'Light Scout Armor',icon:'🥷',role:'Light / mobility',tags:['stealth','objectives','solo'],desc:'Prioritize stamina, speed, and repositioning for reconnaissance.'},
      {name:'Light Engineering Armor',icon:'🧰',role:'Light / utility',tags:['objectives','balanced'],desc:'A mobile option when grenade utility and objective rotation matter.'},
      {name:'Medium Fortified Armor',icon:'🛡️',role:'Medium / defense',tags:['automatons','support','defense'],desc:'A balanced defensive option for exposed firefights.'},
      {name:'Heavy Fortified Armor',icon:'🦾',role:'Heavy / durability',tags:['antiarmor','defense','automatons'],desc:'Durability-focused choice when mobility is less important.'},
      {name:'Medium Medic Armor',icon:'🩺',role:'Medium / sustain',tags:['support','squad'],desc:'Supports longer team engagements and recovery-oriented play.'},
      {name:'Light Servo-Assisted Armor',icon:'🏃',role:'Light / mobility',tags:['stealth','solo','objectives'],desc:'Mobility-focused choice for flanks, rotations, and disengagement.'}
    ],
    enemies: [
      {name:'Charger',icon:'🪲',role:'Terminid / heavy',tags:['terminids','antiarmor'],desc:'Armored rush threat; coordinate heavy damage and avoid its charge lane.'},
      {name:'Bile Titan',icon:'☠️',role:'Terminid / super-heavy',tags:['terminids','antiarmor'],desc:'High-priority heavy target; use team focus and heavy ordnance.'},
      {name:'Bile Spewer',icon:'☣️',role:'Terminid / ranged area denial',tags:['terminids','crowd'],desc:'Acid artillery threat; use cover and avoid grouped positioning.'},
      {name:'Stalker',icon:'👁️',role:'Terminid / ambusher',tags:['terminids','priority'],desc:'Ambush threat; identify and remove quickly when spotted.'},
      {name:'Automaton Devastator',icon:'🤖',role:'Automaton / heavy infantry',tags:['automatons','antiarmor'],desc:'Armored ranged pressure; use cover and target weak points.'},
      {name:'Hulk',icon:'🤖',role:'Automaton / heavy',tags:['automatons','antiarmor'],desc:'Dangerous close/ranged heavy; coordinate anti-armor and flanking.'},
      {name:'Tank',icon:'🛞',role:'Automaton / vehicle',tags:['automatons','antiarmor'],desc:'Vehicle threat; prioritize heavy ordnance and safe approach angles.'},
      {name:'Factory Strider',icon:'🏭',role:'Automaton / super-heavy',tags:['automatons','antiarmor'],desc:'Large fortified threat; squad coordination and sustained heavy damage.'},
      {name:'Illuminate Overseer',icon:'👽',role:'Illuminate / infantry',tags:['illuminate','priority'],desc:'Ranged enemy; use cover and prioritize threats disrupting objectives.'},
      {name:'Harvesters',icon:'🛸',role:'Illuminate / heavy',tags:['illuminate','antiarmor'],desc:'Heavy mobile threat; focus fire and exploit openings.'},
      {name:'Voteless',icon:'🧟',role:'Illuminate / swarm',tags:['illuminate','crowd'],desc:'Swarm pressure; area control and movement prevent being surrounded.'},
      {name:'Watcher',icon:'👁️',role:'Illuminate / scout',tags:['illuminate','priority'],desc:'Detection and reinforcement risk; destroy promptly when possible.'}
    ]
  };
  const roleNames = {area:'Area damage',heavy:'Heavy damage',control:'Crowd control',utility:'Utility',supportWeapon:'Support weapon',defense:'Defense',mobility:'Mobility'};
  const factionNames = {terminids:'Terminids',automatons:'Automatons',illuminate:'Illuminate',unknown:'Unknown / mixed'};
  const state = {baseline:false, lastOrder:'', dispatchIds:new Set(), attacked:new Set(), archive:[], category:'stratagems', lastLoadout:[], equipment:[], loadoutContext:{}, history:{war_snapshots:[],planet_snapshots:[]}, serverPush:false};
  const bridge = () => window.GCBridge || null;
  const planetName = p => String((p && p.name) || `Planet ${p?.index ?? ''}`);
  const planets = () => bridge()?.state?.planets || [];
  const feeds = () => bridge()?.state?.feeds || {};
  const arr = v => { if(Array.isArray(v)) return v; if(v&&typeof v==='object') for(const k of ['items','results','data','planets','campaigns','assignments','dispatches','news']) if(Array.isArray(v[k]))return v[k]; return []; };
  const attacked = p => bridge()?.isAttacked ? bridge().isAttacked(p) : Boolean(p?.attacking);
  const eventPlanet = p => bridge()?.hasEvent ? bridge().hasEvent(p) : Boolean(p?.event);
  const cleanArr = v => arr(v).filter(x => x && typeof x === 'object');
  function translateBase() {
    const es = lang === 'es';
    const text = (selector, value) => { const el=document.querySelector(selector); if(el)el.textContent=value; };
    text('.hero .eyebrow',es?'Democracia administrada // Operaciones en vivo':'Managed Democracy // Live Operations');
    const hero=document.querySelector('.hero h1'); if(hero)hero.innerHTML=es?'LA GALAXIA<br>ES EL CAMPO DE BATALLA.':'THE GALAXY<br>IS THE BATTLEFIELD.';
    text('.hero p',es?'Seguimiento de la guerra, inteligencia de combate y archivos galácticos. Tu panorama operativo para defender Super Tierra.':'Live war tracking, combat intelligence and galactic archives. Your operational picture for the fight for Super Earth.');
    text('.section-head h2',es?'Divisiones de mando':'Command divisions');
    text('.section-head p',es?'Tres pilares conectados en una sola vista operativa.':'Three core pillars, connected through one operational view.');
    const pillarTitles=Array.from(document.querySelectorAll('.pillars .pillar h3'));
    const pillarDesc=Array.from(document.querySelectorAll('.pillars .pillar p'));
    if(pillarTitles.length===3){const titles=es?['Guerra galáctica','Preparación de misión','Archivos e inteligencia comunitaria']:['Galactic War','Mission readiness','Archives & community intel'];pillarTitles.forEach((el,i)=>el.textContent=titles[i]);}
    if(pillarDesc.length===3){const desc=es?['Control planetario, progreso de liberación, campañas, ataques y órdenes globales.','Evalúa la presión enemiga y el contexto operativo para preparar tu misión.','Comunicados, actualizaciones oficiales, historia de guerra y situación comunitaria.']:['Planet control, liberation progress, campaigns, attacks and live global orders.','Track enemy pressure and operational context to inform your next loadout and mission plan.','Dispatches, official updates, war history and the wider community picture.'];pillarDesc.forEach((el,i)=>el.textContent=desc[i]);}
    text('#planets .panel-title h3',es?'Mapa de guerra / Frente planetario':'War map / Planetary front');
    text('#ordersPanel .panel-title h3',es?'Órdenes del día':'Orders of the day');
    const heading = id => { const el=$(id); const h=el?.closest('.panel')?.querySelector('.panel-title h3'); return h; };
    if(heading('dispatches'))heading('dispatches').textContent=es?'Comunicados galácticos':'Galactic dispatches';
    if(heading('steamNews'))heading('steamNews').textContent=es?'Actualizaciones oficiales':'Official updates';
    if(heading('station'))heading('station').textContent=es?'Estación espacial':'Space station';
    const search=$('planetSearch');if(search)search.placeholder=es?'Buscar planeta, sector, bioma o facción…':'Search planet, sector, biome or faction…';
    const filter=$('planetFilter'); if(filter){const labels=es?['Todos los planetas','Bajo ataque','Evento activo','Con datos de progreso']:['All planets','Under attack','Active event','Progress data'];Array.from(filter.options).forEach((o,i)=>{if(labels[i])o.textContent=labels[i];});}
    const more=$('morePlanets'); if(more)more.textContent=es?'Mostrar más planetas':'Show more planets';
    const refresh=$('refreshBtn');if(refresh)refresh.textContent=es?'↻ Actualizar':'↻ Refresh';
    const mapBtn=document.querySelector('.top-actions .btn.primary');if(mapBtn)mapBtn.textContent=es?'Mapa de guerra ↓':'War map ↓';
    const mobile=Array.from(document.querySelectorAll('.mobile-nav a'));const ml=es?['Mapa','Órdenes','Equipamiento','Archivos','Códice']:['War map','Orders','Loadout','Archives','Codex'];mobile.forEach((el,i)=>{if(ml[i])el.lastChild.textContent=ml[i];});
    document.querySelectorAll('[data-catalog]').forEach(b=>{const names={stratagems:es?'Estratagemas':'Stratagems',weapons:es?'Armas':'Weapons',armor:es?'Armaduras':'Armor',enemies:es?'Enemigos':'Enemies'};b.textContent=names[b.dataset.catalog];});
  }
  function applyLanguage() {
    const preserved = Object.fromEntries(['loFaction','loMission','loStyle','loDifficulty','loSquad'].map(id=>[id,$(id)?.value]));
    document.documentElement.lang = lang;
    document.querySelectorAll('[data-i18n]').forEach(el => { const k=el.getAttribute('data-i18n'); if(tr[lang][k]) el.textContent=tr[lang][k]; });
    translateBase();
    $('langToggle').textContent = lang === 'en' ? 'ES' : 'EN';
    $('archiveSearch').placeholder=t('searchArchive'); $('catalogSearch').placeholder=t('searchCatalog');
    const archiveFilter=$('archiveType').value; $('archiveType').innerHTML = `<option value="all">${esc(t('allRecords'))}</option><option value="dispatch">${esc(t('dispatches'))}</option><option value="news">${esc(t('officialNews'))}</option><option value="order">${esc(t('orders'))}</option><option value="event">${esc(t('planetaryEvents'))}</option><option value="history">${esc(t('history'))}</option>`; if([...$('archiveType').options].some(o=>o.value===archiveFilter))$('archiveType').value=archiveFilter;
    $('loFaction').innerHTML = `<option value="terminids">${esc(t('terminids'))}</option><option value="automatons">${esc(t('automatons'))}</option><option value="illuminate">${esc(t('illuminate'))}</option><option value="unknown">${esc(t('unknown'))}</option>`;
    $('loStyle').innerHTML = `<option value="balanced">${esc(t('balanced'))}</option><option value="antiarmor">${esc(t('antiarmor'))}</option><option value="crowd">${esc(t('crowd'))}</option><option value="support">${esc(t('support'))}</option><option value="stealth">${esc(t('stealth'))}</option>`;
    $('loMission').innerHTML = `<option value="eradicate">${esc(t('eradicate'))}</option><option value="defense">${esc(t('defense'))}</option><option value="objectives">${esc(t('objectives'))}</option><option value="recon">${esc(t('recon'))}</option><option value="extermination">${esc(t('extermination'))}</option><option value="escort">${esc(t('escort'))}</option>`;
    $('loSquad').innerHTML = `<option value="solo">${esc(t('solo'))}</option><option value="duo">${esc(t('duo'))}</option><option value="squad">${esc(t('squad'))}</option>`;
    Object.entries(preserved).forEach(([id,value])=>{if(value && [...$(id).options].some(o=>o.value===value))$(id).value=value;});
    renderCatalog(); renderCommunity(); renderArchives(); renderLoadout();
  }
  function updatePlanetSelect() {
    const select=$('loPlanet'), old=select.value;
    const items=planets().slice().sort((a,b)=>planetName(a).localeCompare(planetName(b)));
    select.innerHTML=`<option value="">${esc(t('noPlanet'))}</option>`+items.map(p=>`<option value="${esc(p.index ?? p.name)}">${esc(planetName(p))}${p.sector?' · '+esc(p.sector):''}</option>`).join('');
    if(old && [...select.options].some(o=>o.value===old)) select.value=old;
  }
  const missionTags = {
    eradicate:['crowd','defense','area'], defense:['defense','crowd','control'], objectives:['objectives','balanced','utility'], recon:['stealth','mobility','utility'], extermination:['antiarmor','heavy'], escort:['support','defense','utility']
  };
  function scoreStratagem(item, faction, mission, style, difficulty, squad) {
    let score=0;
    if(item.factions?.includes(faction)) score+=3;
    if(faction==='unknown') score+=1;
    for(const tag of (missionTags[mission]||[])) if(item.tags?.includes(tag)) score+=2.3;
    if(item.tags?.includes(style)) score+=3.5;
    if(Number(difficulty)>=8 && ['heavy','supportWeapon','defense'].includes(item.role)) score+=1.5;
    if(squad==='solo' && item.tags?.includes('solo')) score+=2;
    if(squad==='squad' && ['utility','control','defense'].includes(item.role)) score+=1;
    if(faction==='automatons' && item.tags?.includes('stealth') && mission==='recon') score+=1;
    return score;
  }
  function renderLoadout() {
    const result=$('loadoutResult'); if(!result) return;
    const faction=$('loFaction').value, mission=$('loMission').value, style=$('loStyle').value, difficulty=Number($('loDifficulty').value), squad=$('loSquad').value;
    const p=planets().find(x=>String(x.index ?? x.name)===$('loPlanet').value);
    const ranked=catalog.stratagems.map(item=>({...item,score:scoreStratagem(item,faction,mission,style,difficulty,squad)})).sort((a,b)=>b.score-a.score);
    const selected=[], roles=new Set();
    for(const item of ranked) { const category=item.role==='heavy'||item.role==='supportWeapon'?'antiarmor':item.role==='utility'||item.role==='mobility'?'utility':item.role==='defense'?'defense':'area'; if(!roles.has(category)||selected.length<2){selected.push({...item,category});roles.add(category);} if(selected.length===4)break; }
    while(selected.length<4) { const next=ranked.find(x=>!selected.some(y=>y.name===x.name)); if(!next)break;selected.push({...next,category:next.role}); }
    state.lastLoadout=selected;
    const equipmentScore = item => {
      let score=0;
      if(item.tags?.includes(style))score+=4;
      if(item.tags?.includes(faction))score+=3;
      if(item.tags?.includes(mission))score+=2;
      for(const tag of (missionTags[mission]||[]))if(item.tags?.includes(tag))score+=1.5;
      if(item.tags?.includes('balanced')&&style==='balanced')score+=2;
      if(item.tags?.includes('support')&&squad==='squad')score+=2;
      if(item.tags?.includes('solo')&&squad==='solo')score+=2;
      if(faction==='automatons'&&item.tags?.includes('antiarmor'))score+=2;
      if(faction==='terminids'&&item.tags?.includes('crowd'))score+=2;
      return score;
    };
    const weapon=[...catalog.weapons].sort((a,b)=>equipmentScore(b)-equipmentScore(a))[0];
    const armor=[...catalog.armor].sort((a,b)=>equipmentScore(b)-equipmentScore(a))[0];
    state.equipment=[weapon,armor];
    state.loadoutContext={faction,mission,style,difficulty,squad,planet:p?planetName(p):'',hazards:p?plain(JSON.stringify(p.hazards||[])):''};
    result.innerHTML=selected.map((item,i)=>{const kind=item.role==='supportWeapon'?'Support Weapon':item.role==='defense'?'Sentry':item.role==='utility'||item.role==='mobility'?'Backpack':item.name.toLowerCase().includes('eagle')?'Eagle':'Orbital Strike';const art='https://helldivers.wiki.gg/wiki/Special:FilePath/'+encodeURIComponent(kind+' Stratagem Fallback Icon Background.svg');const wiki='https://helldivers.wiki.gg/wiki/Stratagems';return `<article class="module-card loadout-stratagem-card"><div class="loadout-art"><img src="${art}" alt="${esc(kind)} stratagem icon" loading="lazy" onerror="this.style.display='none';this.parentElement.classList.add('no-art')"><span>${item.icon}</span></div><div class="loadout-stratagem-copy"><div class="meta">${esc(t('fourSlots'))} · ${i+1}/4</div><h4>${esc(item.name)}</h4><p>${esc(item.desc)}</p><div class="module-actions"><span class="tag">${esc(roleNames[item.role]||item.role)}</span><span class="tag">${esc(item.tags.slice(0,2).join(' · '))}</span><a class="tag wiki-source" href="${wiki}" target="_blank" rel="noopener noreferrer">Wiki ↗</a></div></div></article>`;}).join('');
    const hazardText=p?plain(JSON.stringify(p.hazards||[])):'';
    const planetCard=p?`<article class="module-card catalog-card"><div class="catalog-icon">🪐</div><div class="catalog-copy"><div class="meta">${lang==='es'?'CONTEXTO PLANETARIO':'PLANET CONTEXT'}</div><h4>${esc(planetName(p))}</h4><p>${esc([p.sector||'',plain(JSON.stringify(p.biome||'')),hazardText?((lang==='es'?'Riesgos: ':'Hazards: ')+hazardText):'',p.event?((lang==='es'?'Evento: ':'Event: ')+plain(JSON.stringify(p.event))):''].filter(Boolean).join(' · '))}</p></div></article>`:'';
    $('loadoutEquipment').innerHTML=`<article class="module-card catalog-card"><div class="catalog-icon">${weapon.icon}</div><div class="catalog-copy"><div class="meta">${lang==='es'?'ARMA PRINCIPAL SUGERIDA':'SUGGESTED PRIMARY'}</div><h4>${esc(weapon.name)}</h4><p>${esc(weapon.desc)}</p></div></article><article class="module-card catalog-card"><div class="catalog-icon">${armor.icon}</div><div class="catalog-copy"><div class="meta">${lang==='es'?'ARMADURA SUGERIDA':'SUGGESTED ARMOR'}</div><h4>${esc(armor.name)}</h4><p>${esc(armor.desc)}</p></div></article>${planetCard}`;
    $('loStatus').textContent=(p ? `${t('generated')} ${planetName(p)} · ${p.sector||'sector unknown'}${hazardText?' · '+(lang==='es'?'Riesgos: ':'Hazards: ')+hazardText:''}` : t('generated'));
  }
  function renderCatalog() {
    const root=$('catalogList'); if(!root)return;
    const query=$('catalogSearch').value.trim().toLowerCase(), items=(catalog[state.category]||[]).filter(x=>[x.name,x.role,x.desc,...(x.tags||[]),...(x.factions||[])].join(' ').toLowerCase().includes(query));
    document.querySelectorAll('[data-catalog]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.catalog===state.category)));
    root.innerHTML=items.length?items.map(x=>`<article class="module-card catalog-card"><div class="catalog-icon">${x.icon||'◈'}</div><div class="catalog-copy"><h4>${esc(x.name)}</h4><div class="meta">${esc(x.role||'Reference')}</div><p>${esc(x.desc||'')}</p><div class="module-actions">${(x.tags||x.factions||[]).slice(0,4).map(tag=>`<span class="tag">${esc(tag)}</span>`).join('')}</div></div></article>`).join(''):`<div class="module-empty">${esc(t('noMatches'))}</div>`;
  }
  function renderCommunity() {
    const ps=planets(), attacks=ps.filter(attacked), events=ps.filter(eventPlanet), campaigns=cleanArr(feeds().campaigns);
    $('communityKpis').innerHTML=[
      [ps.length,t('trackedPlanets')],[attacks.length,t('attackPlanets')],[events.length,t('activeEvents')],[campaigns.length,t('campaigns')]
    ].map(([n,label])=>`<div class="module-kpi"><strong>${fmt(n)}</strong><span>${esc(label)}</span></div>`).join('');
    const pressure=attacks.slice(0,5).map(p=>`<div class="module-row"><span><strong>${esc(planetName(p))}</strong><small>${esc(p.sector||'Sector unknown')} · ${esc(factionNames[String(p.currentOwner||'').toLowerCase()]||String(p.currentOwner||'Owner unknown'))}</small></span><span class="tag hot">${lang==='es'?'Bajo ataque':'Under attack'}</span></div>`).join('');
    const eventList=events.slice(0,5).map(p=>`<div class="module-row"><span><strong>${esc(planetName(p))}</strong><small>${esc(plain(typeof p.event==='object'?JSON.stringify(p.event):p.event).slice(0,130))}</small></span><span class="tag">${esc(t('event'))}</span></div>`).join('');
    $('communityDetails').innerHTML=`<div><strong>${esc(t('topAttacks'))}</strong>${pressure||`<div class="module-empty">${esc(t('noData'))}</div>`}</div><div style="margin-top:8px"><strong>${esc(t('topEvents'))}</strong>${eventList||`<div class="module-empty">${esc(t('noEvents'))}</div>`}</div>`;
  }
  function buildArchives() {
    const f=feeds(), items=[];
    cleanArr(f.dispatches||f.news).forEach(d=>items.push({type:'dispatch',date:d.published||d.publishedAt||d.id,title:plain(d.title||`Dispatch ${d.id||''}`),text:plain(d.message||d.Message||d.content||''),id:d.id}));
    cleanArr(f.steam_news||f.steam).forEach(n=>items.push({type:'news',date:n.publishedAt||n.published,title:plain(n.title||'Official update'),text:plain(n.content||n.description||''),url:safeHref(n.url)}));
    cleanArr(f.assignments||f.orders).forEach(o=>items.push({type:'order',date:o.expiration||o.expirationAt,title:plain(o.title||o.name||'Major order'),text:plain(o.briefing||o.description||''),id:o.id}));
    planets().filter(eventPlanet).forEach(p=>items.push({type:'event',date:null,title:planetName(p),text:plain(typeof p.event==='object'?JSON.stringify(p.event):p.event),sector:p.sector}));
    (state.history.war_snapshots||[]).forEach(w=>{
      const summary=w.summary&&typeof w.summary==='object'?Object.entries(w.summary).slice(0,8).map(([k,v])=>`${k}: ${typeof v==='object'?JSON.stringify(v):v}`).join(' · '):'';
      items.push({type:'history',date:w.captured_at,title:`${lang==='es'?'Captura de guerra':'War snapshot'}${w.war_id?' · '+w.war_id:''}`,text:plain(summary||'Persisted war data snapshot')});
    });
    (state.history.planet_snapshots||[]).slice(0,20).forEach(p=>{
      const health=p.health==null?'—':fmt(p.health), max=p.max_health==null?'—':fmt(p.max_health);
      items.push({type:'history',date:p.captured_at,title:plain(p.name||`Planet ${p.upstream_index}`),text:`${lang==='es'?'Salud':'Health'} ${health}/${max}${p.player_count==null?'':` · ${fmt(p.player_count)} ${lang==='es'?'jugadores':'players'}`}`});
    });
    return items.sort((a,b)=>dateScore(b.date)-dateScore(a.date));
  }
  function dateScore(d) { if(!d)return 0; const n=typeof d==='number'?(d<1e12?d*1000:d):Date.parse(d);return Number.isFinite(n)?n:0; }
  function renderArchives() {
    const root=$('archiveList'); if(!root)return;
    state.archive=buildArchives();
    const q=$('archiveSearch').value.trim().toLowerCase(), type=$('archiveType').value;
    const items=state.archive.filter(x=>(type==='all'||x.type===type)&&[x.title,x.text,x.type,x.sector].join(' ').toLowerCase().includes(q)).slice(0,80);
    root.innerHTML=items.length?items.map(x=>`<article class="archive-item"><div class="archive-date">${esc(x.type.toUpperCase())}${x.date?' · '+esc(bridge()?.dateText(x.date)||String(x.date)):''}</div><h4>${x.url?`<a href="${esc(x.url)}" target="_blank" rel="noopener noreferrer">${esc(x.title)}</a>`:esc(x.title)}</h4><p>${esc(x.text.slice(0,360))}${x.sector?' · '+esc(x.sector):''}</p></article>`).join(''):`<div class="module-empty">${esc(t('noMatches'))}</div>`;
  }
  function notificationStatus(text) { $('alertStatus').textContent=text; }
  function notify(title, body, tag) {
    if(state.serverPush || !('Notification' in window)||Notification.permission!=='granted')return;
    try { const n=new Notification(title,{body,tag}); n.onclick=()=>{window.focus();n.close();}; } catch(_) {}
  }
  function checkAlerts() {
    const f=feeds(), orders=cleanArr(f.assignments||f.orders), dispatches=cleanArr(f.dispatches||f.news), ps=planets();
    if(!Object.keys(f).length && ps.length===0) return;
    const order=orders.map(x=>`${x.id||''}:${x.title||''}:${x.expiration||''}`).join('|');
    const ids=new Set(dispatches.map(x=>String(x.id??x.published??x.title)));
    const attackNow=new Set(ps.filter(attacked).map(p=>String(p.index??p.name)));
    if(state.baseline) {
      if($('alertOrderToggle').checked && order && order!==state.lastOrder) notify(t('orderChanged'),plain(orders[0]?.title||'A major order has changed.'),'gc-order');
      if($('alertDispatchToggle').checked) for(const id of ids) if(!state.dispatchIds.has(id)){ const d=dispatches.find(x=>String(x.id??x.published??x.title)===id); notify(t('newDispatch'),plain(d?.title||d?.message||'A new dispatch is available.'),'gc-dispatch-'+id); }
      if($('alertAttackToggle').checked) for(const id of attackNow) if(!state.attacked.has(id)){const p=ps.find(x=>String(x.index??x.name)===id);notify(t('planetAttacked'),planetName(p),'gc-planet-'+id);}
    }
    state.lastOrder=order; state.dispatchIds=ids; state.attacked=attackNow; state.baseline=true;
    if('Notification' in window && Notification.permission==='granted') notificationStatus(t('notificationsEnabled'));
    else notificationStatus(t('notificationsDisabled'));
  }
  const basePath = (() => { const p=window.location.pathname; if(p.endsWith('/')) return p; return p.replace(/[^/]*$/,''); })();
  const apiUrl = path => `${basePath}${path.startsWith('/') ? path.slice(1) : path}`;
  function decodeVapidKey(base64String) {
    const padding='='.repeat((4-base64String.length%4)%4);
    const base64=(base64String+padding).replace(/-/g,'+').replace(/_/g,'/');
    const raw=atob(base64); const out=new Uint8Array(raw.length);
    for(let i=0;i<raw.length;i++)out[i]=raw.charCodeAt(i);
    return out;
  }
  async function tryEnableServerPush() {
    if(!('serviceWorker' in navigator)||!('PushManager' in window)) throw new Error('Push API is not supported by this browser.');
    const keyResponse=await fetch(apiUrl('api/v1/push/public-key'),{headers:{Accept:'application/json'},cache:'no-store'});
    const keyPayload=await keyResponse.json();
    if(!keyResponse.ok||keyPayload?.ok===false) throw new Error(keyPayload?.error?.message||'Server push is not configured.');
    const registration=await navigator.serviceWorker.register(apiUrl('sw.js'),{scope:basePath});
    const existing=await registration.pushManager.getSubscription();
    const subscription=existing||await registration.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:decodeVapidKey(keyPayload.data.public_key)});
    const response=await fetch(apiUrl('api/v1/push/subscribe'),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({subscription:subscription.toJSON(),preferences:currentPushPreferences()})});
    const body=await response.json();
    if(!response.ok||body?.ok===false) throw new Error(body?.error?.message||'Could not save push subscription.');
    state.serverPush=true;
    return true;
  }
  async function enableAlerts() {
    if(!('Notification' in window)){notificationStatus(t('notificationUnsupported'));return;}
    const permission=await Notification.requestPermission();
    if(permission!=='granted'){notificationStatus(permission==='denied'?t('notificationDenied'):t('notificationsDisabled'));return;}
    notificationStatus(t('notificationsEnabled')); checkAlerts();
    try { await tryEnableServerPush(); notificationStatus(lang==='es'?'Alertas locales y push del servidor activadas.':'Local alerts and server push are enabled.'); }
    catch(err) { notificationStatus((lang==='es'?'Alertas locales activadas; push del servidor no disponible: ':'Local alerts enabled; server push unavailable: ')+err.message); }
  }
  async function disableServerPush() {
    try {
      if(!('serviceWorker' in navigator)){notificationStatus(lang==='es'?'Este navegador no tiene service worker.':'Service workers are not supported.');return;}
      const registration=await navigator.serviceWorker.getRegistration(apiUrl('sw.js'));
      const subscription=registration ? await registration.pushManager.getSubscription() : null;
      if(subscription) {
        const endpoint=subscription.endpoint;
        await subscription.unsubscribe();
        await fetch(apiUrl('api/v1/push/subscribe'),{method:'DELETE',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({endpoint})});
      }
      state.serverPush=false;
      notificationStatus(lang==='es'?'Suscripción push desactivada. Las notificaciones locales dependen del permiso del navegador.':'Push subscription disabled. Local alerts still depend on browser permission.');
    } catch(err) {
      notificationStatus((lang==='es'?'No se pudo desactivar push: ':'Could not disable push: ')+err.message);
    }
  }
  function currentPushPreferences() {
    return {orders:$('alertOrderToggle').checked,dispatches:$('alertDispatchToggle').checked,attacks:$('alertAttackToggle').checked};
  }
  async function syncPushPreferences() {
    try {
      const registration=await navigator.serviceWorker?.getRegistration(apiUrl('sw.js'));
      const subscription=registration?await registration.pushManager.getSubscription():null;
      if(!subscription)return;
      await fetch(apiUrl('api/v1/push/subscribe'),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({subscription:subscription.toJSON(),preferences:currentPushPreferences()})});
    } catch (_) {}
  }
  function applySavedAlertSettings() {
    for(const id of ['alertOrderToggle','alertDispatchToggle','alertAttackToggle']) {
      const saved=localStorage.getItem('gc-'+id);
      if(saved!==null)$(id).checked=saved==='true';
      $(id).addEventListener('change',()=>{localStorage.setItem('gc-'+id,String($(id).checked));syncPushPreferences();});
    }
    if('Notification' in window && Notification.permission==='granted')notificationStatus(t('notificationsEnabled'));
    else notificationStatus(t('notificationsDisabled'));
  }
  function copyLoadout() {
    const c=state.loadoutContext;
    const context=`Faction: ${c.faction||'—'} | Mission: ${c.mission||'—'} | Playstyle: ${c.style||'—'} | Difficulty: ${c.difficulty||'—'} | Squad: ${c.squad||'—'}${c.planet?' | Planet: '+c.planet:''}`;
    const gear=state.equipment.map(x=>`${x.name} — ${x.desc}`).join('\n');
    const text=[context,'','Stratagems:',...state.lastLoadout.map((x,i)=>`${i+1}. ${x.name} — ${x.desc}`),'','Equipment:',gear].join('\n');
    if(!state.lastLoadout.length)return;
    if(navigator.clipboard?.writeText) navigator.clipboard.writeText(text).then(()=>{$('loStatus').textContent=t('copied');}).catch(()=>{$('loStatus').textContent=t('copyError');});
    else {$('loStatus').textContent=t('copyUnsupported');}
  }
  async function loadHistory() {
    try {
      const response=await fetch(apiUrl('api/v1/history?limit=30'),{headers:{Accept:'application/json'},cache:'no-store'});
      const body=await response.json();
      if(response.ok&&body?.ok!==false&&body?.data)state.history=body.data;
    } catch (_) {}
    renderArchives();
  }
  function refreshModules() {
    if(!bridge())return;
    updatePlanetSelect(); renderLoadout(); renderCommunity(); renderArchives(); checkAlerts();
    loadHistory();
  }
  $('langToggle').addEventListener('click',()=>{lang=lang==='en'?'es':'en';localStorage.setItem('gc-lang',lang);applyLanguage();});
  $('loGenerate').addEventListener('click',renderLoadout);
  ['loFaction','loMission','loStyle','loDifficulty','loSquad','loPlanet'].forEach(id=>$(id).addEventListener('change',renderLoadout));
  $('loCopy').addEventListener('click',copyLoadout);
  $('catalogSearch').addEventListener('input',renderCatalog);
  $('catalogTabs').addEventListener('click',e=>{const b=e.target.closest('[data-catalog]');if(!b)return;state.category=b.dataset.catalog;renderCatalog();});
  $('archiveSearch').addEventListener('input',renderArchives); $('archiveType').addEventListener('change',renderArchives);
  $('enableAlerts').addEventListener('click',enableAlerts); $('disablePush').addEventListener('click',disableServerPush);
  applySavedAlertSettings(); applyLanguage(); refreshModules();
  if('serviceWorker' in navigator) navigator.serviceWorker.getRegistration(apiUrl('sw.js')).then(reg=>reg?reg.pushManager.getSubscription():null).then(sub=>{state.serverPush=Boolean(sub);}).catch(()=>{});
  window.addEventListener('gc:data-updated',refreshModules);
})();