# Architecture Documentation

## Level 0 DFD
```mermaid
  graph TD;
      A[System] -->|Input| B[User];
      A -->|Output| C[External System];
```  

## Level 1 DFDs for All Modules

### Module 1 DFD
```mermaid
  graph TD;
      A[Module 1] -->|User Command| B[Subsystem 1];
      B -->|Data| C[Database 1];
      C -->|Response| A;
```  

### Module 2 DFD
```mermaid
  graph TD;
      A[Module 2] -->|User Input| B[Subsystem 2];
      B -->|Processing| C[External API];
      C -->|Response| A;
```  

## ER Diagram
```mermaid
  erDiagram
      USER ||--o{ ORDER : places
      ORDER ||--|{ LINE_ITEM : contains
      LINE_ITEM }|--|| PRODUCT : includes
```  

---

*This document provides an overview of the architecture of the system, showcasing the major components and their interactions.*