/**
 * Projects Module - Task model
 */
export interface Task {
  id: string;
  title: string;
  status: TaskStatus;
  projectId: string;
  projectName: string;
}

export type TaskStatus = 'todo' | 'in_progress' | 'blocked' | 'review' | 'done';
