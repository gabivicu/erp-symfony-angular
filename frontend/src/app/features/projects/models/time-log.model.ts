/**
 * Projects Module - Time Log model
 */
export interface TimeLog {
  id: string;
  description: string;
  hours: number;
  loggedDate: string;
  projectId: string;
  projectName: string;
  taskId: string | null;
  taskTitle: string | null;
}
