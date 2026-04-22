import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL

export function useLogger() {
  async function logAction(actionName, dataObj = {}) {
    try {
      await axios.post(`${API_URL}/api/logs`, {
        action: actionName,
        meeting_code: dataObj.meeting_code ?? null,
        user_id: dataObj.user_id ?? null,
        metadata: dataObj,
      })
    } catch {
      // Logging failures are silent — never block the UI
    }
  }

  return { logAction }
}
