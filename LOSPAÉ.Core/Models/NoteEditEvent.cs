using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace LOSPAÉ;
public class NoteEditEvent
{
    public string EtudiantName { get; set; }
    public string NoteChangeId { get; set; }
    public string NoteChangeDesc { get; set; }
    public DateTime ChangedOccurDateTime { get; set; }

    public NoteEditEvent(string etudiantName, string noteChangeId, string noteChangeDesc)
    {
        EtudiantName = etudiantName;
        NoteChangeId = noteChangeId;
        NoteChangeDesc = noteChangeDesc;
        ChangedOccurDateTime = DateTimeOffset.Now.LocalDateTime;
    }
}
