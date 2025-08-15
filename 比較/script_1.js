// 利用者詳細画面をレンダリング
function renderClientDetails(client) {
    const detailsDiv = document.getElementById('client-details');
    currentClient = client; // グローバル変数に現在のクライアントをセット

    if (!client) {
        detailsDiv.innerHTML = '<p class="no-client-selected">利用者を選択してください</p>';
        return;
    }

    const latestRecord = client.records.length > 0 ? client.records[0] : null;

    let recordsHtml = '';
    if (client.records.length > 0) {
        recordsHtml = `
            ${client.records.map(record => `
                <div class="record-item">
                    <span class="record-date">${record.recordDate}</span>
                    <span class="record-type ${record.recordType}">${record.recordType}</span>
                    <p class="record-details">${formatTextWithLineBreaks(record.details)}</p>
                </div>
            `).join('')}
        `;
    } else {
        recordsHtml = '<p>記録はまだありません。</p>';
    }

    detailsDiv.innerHTML = `
        <div class="client-header">
            <h2 class="client-name">${client.client_name}</h2>
            <div class="client-actions">
                <button class="action-button" onclick="openRecordModal(currentClient.id)">記録を追加</button>
                <button class="action-button" onclick="openEditClientModal()">編集</button>
            </div>
        </div>
        <div class="client-body">
            <div class="client-section">
                <h3>基本情報</h3>
                <p><strong>生年月日:</strong> ${client.dateOfBirth}</p>
                <p><strong>利用開始日:</strong> ${client.enrollmentDate}</p>
                <p><strong>最終更新日:</strong> ${client.lastUpdated}</p>
                <p><strong>最終更新者:</strong> ${client.lastUpdatedBy}</p>
            </div>
            <div class="client-section">
                <h3>最新の状況</h3>
                <p><strong>生活状況:</strong> ${client.latestLifeStatus || '未記録'}</p>
                <p><strong>職業訓練:</strong> ${client.latestTrainingStatus || '未記録'}</p>
                <p><strong>就職活動:</strong> ${client.latestJobHuntingStatus || '未記録'}</p>
            </div>
            <div class="client-section">
                <h3>連絡先</h3>
                <p class="contact-info">${formatTextWithLineBreaks(client.contactInfo)}</p>
            </div>
            <div class="client-section">
                <h3>記録</h3>
                <div class="records-container">
                    ${recordsHtml}
                </div>
            </div>
        </div>
    `;

    // 💡 currentClientがセットされた後、ボタンを動的に追加
    const actionsDiv = detailsDiv.querySelector('.client-actions');
    if (actionsDiv) {
        const jobButton = document.createElement('button');
        jobButton.className = 'action-button';
        jobButton.textContent = '求人情報';
        jobButton.onclick = () => openClientJobsModal(currentClient.id, currentClient.client_name);
        actionsDiv.appendChild(jobButton);
    }
}