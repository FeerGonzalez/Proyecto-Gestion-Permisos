export interface User {
  id: number;
  name: string;
  email: string;
  role: 'empleado' | 'supervisor' | 'rrhh';
  horas_disponibles: number;
  deleted_at?: string | null;
}
