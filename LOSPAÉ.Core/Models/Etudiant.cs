using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace LOSPAÉ;
public class Etudiant
{
    public string EtudiantName { get; set; }
    public string EtudiantSpe { get; set; }
    public double EtudiantNote { get; set; }
    public string EtudiantClassName { get; set; }

    public Etudiant(string etudiantName, string etudiantSpe, double etudiantNote, string etudiantClassName)
    {
        EtudiantName = etudiantName;
        EtudiantSpe = etudiantSpe;
        EtudiantNote = etudiantNote;
        EtudiantClassName = etudiantClassName;
    }
}
