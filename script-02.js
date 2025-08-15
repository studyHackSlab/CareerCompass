<script>
    // PHPから取得したデータをJavaScript変数に格納
    const clients = <?= json_encode($clients, JSON_UNESCAPED_UNICODE); ?>;
    const records = <?= json_encode($records, JSON_UNESCAPED_UNICODE); ?>;
    const currentUserId = <?= json_encode($current_user_id); ?>;
    const currentUserName = <?= json_encode($current_user_name, JSON_UNESCAPED_UNICODE); ?>;

    let selectedClientId = null;

    function formatDate(dateString) {
        if (!dateString) return '---';
        const date = new Date(dateString);
        return date.toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function formatDateTime(dateString) {
        if (!dateString) return '---';
        const date = new Date(dateString);
        return date.toLocaleString('ja-JP', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getRecordTypeClass(type) {
        switch (type) {
            case '生活':
                return 'life';
            case '職業訓練':
                return 'training';
            case '就活':
                return 'job';
            default:
                return '';
        }
    }

    function renderClients(filteredClients = clients) {
        console.log('renderClientsが実行されました。');
        const clientList = document.getElementById('clientList');
        if (filteredClients.length === 0) {
            clientList.innerHTML = '<p style="text-align: center; opacity: 0.7; padding: 1rem;">該当する利用者が見つかりません</p>';
            return;
        }

        clientList.innerHTML = filteredClients.map(client => `
            <div class="client-item ${selectedClientId === client.id ? 'active' : ''}" onclick="selectClient(${client.id})">
                <div class="client-name">${client.name}</div>
                <div class="client-meta">
                    <span>入所: ${formatDate(client.enrollmentDate)}</span>
                    <span>年齢: ${client.dateOfBirth ? new Date().getFullYear() - new Date(client.dateOfBirth).getFullYear() + '歳' : '---'}</span>
                </div>
            </div>
        `).join('');
        console.log('クライアントリストがレンダリングされました。');
    }

    function renderClientDetails(client) {
        console.log('renderClientDetailsが実行されました。', client);
        const clientDetailsContent = document.getElementById('clientDetailsContent');
        if (!client) {
            clientDetailsContent.innerHTML =
                '<p style="text-align: center; opacity: 0.7; padding: 2rem;">利用者を選択してください</p>';
            return;
        }

        const clientRecords = records.filter(record => record.clientId === client.id);
        
        clientDetailsContent.innerHTML = `
            <div class="detail-section">
                <div class="detail-title">📝 基本情報</div>
                <div class="detail-content">
                    <p><strong>名前:</strong> ${client.name}</p>
                    <p><strong>生年月日:</strong> ${formatDate(client.dateOfBirth)}</p>
                    <p><strong>入所日:</strong> ${formatDate(client.enrollmentDate)}</p>
                    <p><strong>最終更新:</strong> ${client.lastUpdated ? formatDateTime(client.lastUpdated) : '未更新'} (${client.lastUpdatedBy ? client.lastUpdatedBy : '---'})</p>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-title">🏠 生活状況</div>
                <div class="detail-content">${client.latestLifeStatus || '記録がありません'}</div>
            </div>

            <div class="detail-section">
                <div class="detail-title">🎓 職業訓練状況</div>
                <div class="detail-content">${client.latestTrainingStatus || '記録がありません'}</div>
            </div>

            <div class="detail-section">
                <div class="detail-title">💼 就活状況</div>
                <div class="detail-content">${client.latestJobHuntingStatus || '記録がありません'}</div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openRecordModal()">記録を追加</button>
                <button class="btn btn-secondary" onclick="editClient(${client.id})">情報を編集</button>
            </div>

            <div class="detail-section">
                <div class="detail-title">📋 過去の記録</div>
                ${clientRecords.length > 0 ?
                    clientRecords.map(record => `
                    <div class="record-item">
                        <div class="record-header">
                            <span class="record-type ${getRecordTypeClass(record.recordType)}">${record.recordType}</span>
                            <span class="record-date">${formatDateTime(record.recordDate)}</span>
                        </div>
                        <div class="record-content">${record.details}</div>
                    </div>
                `).join('') : '<div class="detail-content">記録がありません</div>'}
            </div>
        `;
        console.log('利用者詳細がレンダリングされました。');
    }

    // 修正: clientIdをNumber型に変換し、エラーハンドリングを追加
    function selectClient(clientId) {
        try {
            console.log('selectClientが実行されました。clientId:', clientId);
            selectedClientId = Number(clientId);
            const client = clients.find(c => Number(c.id) === selectedClientId);

            if (client) {
                renderClients(); // アクティブ状態を更新するために再レンダリング
                renderClientDetails(client);
            } else {
                console.error('指定されたclientIdの利用者が見つかりませんでした。', selectedClientId);
            }
        } catch (e) {
            console.error('selectClient実行中にエラーが発生しました:', e);
        }
    }

    function searchClients() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const filteredClients = clients.filter(client =>
            client.name.toLowerCase().includes(searchTerm)
        );
        renderClients(filteredClients);
    }

    function openRecordModal() {
        if (!selectedClientId) {
            alert('利用者を選択してください');
            return;
        }
        document.getElementById('recordModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('recordModal').style.display = 'none';
        document.getElementById('recordForm').reset();
    }

    function addRecord(event) {
        event.preventDefault();
        const recordType = document.getElementById('recordType').value;
        const recordDetails = document.getElementById('recordDetails').value;

        if (!recordType || !recordDetails) {
            alert('すべての項目を入力してください');
            return;
        }

        // AJAXでPHPにデータを送信
        const formData = new FormData();
        formData.append('clientId', selectedClientId);
        formData.append('recordType', recordType);
        formData.append('recordDetails', recordDetails);
        formData.append('recorded_by_user_id', currentUserId); // ユーザーIDを追加

        fetch('add_record.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // 成功した場合、フロントエンドのデータを更新
                const newRecord = data.newRecord;
                records.unshift(newRecord);

                // 最新の記録としてclientsテーブルも更新
                const client = clients.find(c => Number(c.id) === selectedClientId);
                if (client) {
                    if (recordType === '生活') {
                        client.latestLifeStatus = recordDetails;
                    } else if (recordType === '職業訓練') {
                        client.latestTrainingStatus = recordDetails;
                    } else if (recordType === '就活') {
                        client.latestJobHuntingStatus = recordDetails;
                    }
                    client.lastUpdated = newRecord.recordDate;
                    client.lastUpdatedBy = newRecord.recordedBy; // 記録したユーザー名を更新
                }

                renderClientDetails(client);
                closeModal();
                alert('記録を追加しました');
            } else {
                alert('記録の追加に失敗しました: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function editClient(clientId) {
        alert('編集機能は実装予定です');
    }

    function logout() {
        if (confirm('ログアウトしますか？')) {
            window.location.href = 'logout.php';
        }
    }

    // イベントリスナーの設定
    document.getElementById('searchInput').addEventListener('input', searchClients);
    document.getElementById('recordForm').addEventListener('submit', addRecord);

    // ページ読み込み時の初期化
    document.addEventListener('DOMContentLoaded', function() {
        renderClients();
        renderClientDetails(null);
    });

    // モーダル外クリックで閉じる
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('recordModal');
        if (event.target === modal) {
            closeModal();
        }
    });
</script>