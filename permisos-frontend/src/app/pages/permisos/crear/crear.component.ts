import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  Validators,
  ReactiveFormsModule,
  AbstractControl,
  ValidationErrors
} from '@angular/forms';
import { PermisoService } from '../../../core/services/permiso.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-crear',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './crear.component.html',
  styleUrl: './crear.component.css'
})
export class CrearComponent implements OnInit {

  form!: FormGroup;
  horasDisponibles = 0;
  error = '';
  minFecha!: string;

  constructor(
    private fb: FormBuilder,
    private permisoService: PermisoService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.minFecha = new Date().toISOString().split('T')[0];

    this.form = this.fb.group({
      fecha: ['', [Validators.required, this.fechaNoPasada]],
      hora_inicio: ['', Validators.required],
      hora_fin: ['', Validators.required],
      motivo: ['', [Validators.required, Validators.minLength(5)]],
    }, {
      validators: this.validarHorarioLaboral
    });
    
    this.form.get('fecha')?.setValue(this.minFecha);
    this.form.get('fecha')?.updateValueAndValidity();

    this.form.get('fecha')?.valueChanges.subscribe(() => {
      this.form.get('hora_inicio')?.updateValueAndValidity();
    });

    this.cargarHorasDisponibles();
  }

  cargarHorasDisponibles() {
    this.permisoService.horasDisponibles().subscribe({
      next: res => this.horasDisponibles = res.horas_disponibles
    });
  }

  /** Fecha >= hoy */
  fechaNoPasada(control: AbstractControl): ValidationErrors | null {
    if (!control.value) return null;

    const hoy = new Date().toISOString().split('T')[0];
    return control.value < hoy ? { fechaPasada: true } : null;
  }

  /** Horario laboral 07:30 a 13:30 */
  validarHorarioLaboral(control: AbstractControl): ValidationErrors | null {
    const fecha = control.get('fecha')?.value; // Formato "YYYY-MM-DD"
    const inicio = control.get('hora_inicio')?.value; // Formato "HH:mm"
    const fin = control.get('hora_fin')?.value;

    if (!fecha || !inicio || !fin) return null;

    if (inicio < '07:30' || fin > '13:30' || fin <= inicio) {
      return { horarioInvalido: true };
    }

    const hoy = new Date();
    
    const anio = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    const hoyString = `${anio}-${mes}-${dia}`;

    if (fecha === hoyString) {
      const horaActual = hoy.getHours().toString().padStart(2, '0') + ':' + 
                        hoy.getMinutes().toString().padStart(2, '0');

      if (inicio <= horaActual) {
        return { horaPasadaHoy: true };
      }
    }

    return null;
  }

  submit() {
    this.error = '';

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.permisoService.crear(this.form.value).subscribe({
      next: () => this.router.navigate(['/permisos']),
      error: err => {
        this.error = err.error?.message || 'Error al crear el permiso';
      }
    });
  }

  volver() {
    this.router.navigate(['/permisos']);
  }
}
